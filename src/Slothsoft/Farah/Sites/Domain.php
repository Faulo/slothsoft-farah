<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\EmptySitemapException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Zend\Router\Http\RouteMatch;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Domain
{

    const TAG_INCLUDE_PAGES = 'include-pages';

    const TAG_SITEMAP = 'sitemap';

    const TAG_DOMAIN = 'domain';

    const TAG_PAGE = 'page';

    private $file;

    private $document;

    private $domainNode;

    private $domainName;

    private $xpath;

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    private function loadDocument()
    {
        if (! $this->document) {
            
            $this->document = $this->asset->lookupExecutable()
                ->lookupXmlResult()
                ->lookupDOMWriter()
                ->toDocument();
            $this->xpath = DOMHelper::loadXPath($this->document, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_PHP);
            $this->domainNode = $this->document->documentElement;
            
            $node = $this->domainNode;
            if (! $node) {
                throw new EmptySitemapException($this->asset->getId());
            }
            $this->domainName = $this->domainNode->getAttribute('name');
            
            $this->init();
        }
    }

    private function init()
    {
        // preload all include-pages elements
        while ($nodeList = $this->document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, self::TAG_INCLUDE_PAGES) and $nodeList->length) {
            $dataNodeList = [];
            foreach ($nodeList as $node) {
                $dataNodeList[] = $node;
            }
            foreach ($dataNodeList as $dataNode) {
                $url = $this->lookupAssetUrl($dataNode);
                $result = Module::resolveToResult($url);
                $node = $result->toElement($this->document);
                while ($node->hasChildNodes()) {
                    $dataNode->parentNode->insertBefore($node->firstChild, $dataNode);
                }
                $dataNode->parentNode->removeChild($dataNode);
            }
        }
        $this->initDomainElement($this->domainNode);
        $nodeList = $this->document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, self::TAG_PAGE);
        foreach ($nodeList as $node) {
            $this->initPageElement($node);
        }
    }

    private function initDomainElement(DOMElement $node)
    {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', '/');
        $node->setAttribute('url', "{$this->getProtocol()}://{$this->getDomainName()}/");
    }

    private function initPageElement(DOMElement $node)
    {
        $name = $node->getAttribute('name');
        if ($node->hasAttribute('ext')) {
            $uri = $node->getAttribute('ext');
        } else {
            $parentUri = $node->parentNode->getAttribute('uri');
            $uri = $parentUri . $name . '/';
        }
        
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $name);
        }
        $node->setAttribute('uri', $uri);
        $node->setAttribute('url', "{$this->getProtocol()}://{$this->getDomainName()}$uri");
    }

    public function getDocument(): DOMDocument
    {
        $this->loadDocument();
        
        return $this->document;
    }

    public function lookupPageNode(string $path, DOMElement $contextNode = null): DOMElement
    {
        $this->loadDocument();
        
        $path = str_pad($path, 1, '/');
        if ($contextNode === null or $path[0] === '/') {
            $contextNode = $this->domainNode;
        }
        $query = $this->path2expr($path, 'sfs:' . self::TAG_PAGE);
        $pageNode = $this->xpath->evaluate($query, $contextNode)->item(0);
        
        if (! $pageNode) {
            throw new PageNotFoundException($path);
        }
        
        if ($pageNode->hasAttribute('redirect')) {
            $redirectPath = $pageNode->getAttribute('redirect');
            switch ($redirectPath) {
                case '..':
                    $redirectPath = $pageNode->parentNode->getAttribute('uri');
                    break;
            }
            $redirectNode = $this->lookupPageNode($redirectPath, $pageNode);
            throw new PageRedirectionException($redirectNode->getAttribute('uri'));
        }
        
        if ($pageNode->getAttribute('uri') !== $path) {
            throw new PageRedirectionException($pageNode->getAttribute('uri'));
        }
        
        return $pageNode;
    }

    public function lookupAssetUrl(DOMElement $dataNode, array $args = []): FarahUrl
    {
        $vendorName = $this->findVendorName($dataNode);
        $moduleName = $this->findModuleName($dataNode);
        $args += $this->findParameters($dataNode);
        $ref = $dataNode->getAttribute('ref');
        
        return FarahUrl::createFromReference($ref, FarahUrl::createFromComponents(FarahUrlAuthority::createFromVendorAndModule($vendorName, $moduleName), null, FarahUrlArguments::createFromValueList($args)));
    }

    private function path2expr(string $path, string $elementName = '*'): string
    {
        $path = array_filter(explode('/', $path), 'strlen');
        $qry = [
            '.'
        ];
        foreach ($path as $folder) {
            $qry[] = $elementName . '[php:functionString("strtolower", @name) = "' . strtolower($folder) . '" or ancestor-or-self::*/@name = "*"]';
        }
        return implode('/', $qry);
    }

    // @deprecated
    private function findUri(DOMElement $pageNode): string
    {
        if ($pageNode->hasAttribute('ext')) {
            return $pageNode->getAttribute('ext');
        } else {
            return $pageNode->parentNode->getAttribute('uri') . '/' . $pageNode->getAttribute('name');
        }
        $ret = '/';
        $tmpNode = $pageNode;
        while ($tmpNode and $tmpNode->localName !== self::TAG_DOMAIN) {
            if ($tmpNode->hasAttribute('uri') and ! $tmpNode->hasAttribute('redirect')) {
                $ret = $tmpNode->getAttribute('uri') . substr($ret, 1);
                break;
            }
            $ret = '/' . $tmpNode->getAttribute('name') . $ret;
            $tmpNode = $tmpNode->parentNode;
        }
        if ($pageNode->hasAttribute('redirect')) {
            $redirect = $pageNode->getAttribute('redirect');
            if ($redirect[0] === '/') {
                $ret = $redirect;
            } else {
                $ret = explode('/', $ret);
                $redirect = explode('/', $redirect);
                array_pop($ret);
                while (reset($redirect) === '..') {
                    array_pop($ret);
                    array_shift($redirect);
                }
                $ret[] = '';
                $redirect[] = '';
                $ret = implode('/', $ret) . implode('/', $redirect);
            }
        }
        return $ret;
    }

    private function getDomainName(): string
    {
        return $this->domainName;
    }

    private function getProtocol(): string
    {
        return 'http'; // TODO: where to put this?
    }

    private function findModuleName(DOMElement $node): string
    {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@module][1]/@module)', $node);
    }

    private function findVendorName(DOMElement $node): string
    {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@vendor][1]/@vendor)', $node);
    }

    private function findParameters(DOMElement $node): array
    {
        $ret = [];
        foreach ($this->xpath->evaluate('ancestor-or-self::*/sfm:param', $node) as $paramNode) {
            $ret[$paramNode->getAttribute('name')] = $paramNode->getAttribute('value');
        }
        return $ret;
    }
    
    public function toRoutes() : array {
        $this->loadDocument();
        return [$this->domainName => $this->toRoute($this->domainNode, FarahUrl::createFromReference('farah://slothsoft@farah/'))];
    }
    private function toRoute(DOMElement $pageNode, FarahUrl $contextUrl) : array {
        if ($pageNode->hasAttribute('module')) {
            $module = $pageNode->getAttribute('module');
            if ($pageNode->hasAttribute('vendor')) {
                $vendor = $pageNode->getAttribute('vendor');
            } else {
                $vendor = $contextUrl->getUserInfo();
            }
            $contextUrl = $contextUrl->withAssetAuthority(FarahUrlAuthority::createFromVendorAndModule($vendor, $module));
        }
        switch ($pageNode->localName) {
            case 'domain':
                $pageName = '/';
                break;
            case 'page':
                $pageName = $pageNode->getAttribute('name') . '/';
                break;
        }
        
        $children = [];
        $params = [];
        foreach ($pageNode->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                switch ($node->localName) {
                    case 'param':
                        $params[$node->getAttribute('name')] = $node->getAttribute('value');
                        break;
                    case 'page':
                        $children[] = $this->toRoute($node, $contextUrl);
                        break;
                }
            }
        }
        $route = [
            'type' => 'literal',
            'options' => [
                'route' => $pageName,
                'defaults' => $params,
            ],
            'child_routes' => $children,
        ];
        
        if ($pageNode->hasAttribute('ref')) {
            $route['may_terminate'] = true;
            $ref = $pageNode->getAttribute('ref');
            $route['options']['defaults']['asset'] = (string) FarahUrl::createFromReference($ref, $contextUrl);
        }
        RouteMatch::class;
        return $route;
    }
}


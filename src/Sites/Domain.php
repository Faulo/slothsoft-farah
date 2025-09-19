<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use Laminas\Router\Http\RouteMatch;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Domain {
    
    public const CURRENT_SITEMAP = 'farah://slothsoft@farah/current-sitemap';
    
    public static function createWithDefaultSitemap(): Domain {
        $url = FarahUrl::createFromReference(self::CURRENT_SITEMAP);
        return new self(Module::resolveToDOMWriter($url)->toDocument());
    }
    
    public const TAG_INCLUDE_PAGES = 'include-pages';
    
    public const TAG_SITEMAP = 'sitemap';
    
    public const TAG_DOMAIN = 'domain';
    
    public const TAG_PAGE = 'page';
    
    public const TAG_FILE = 'file';
    
    public const TAG_PARAM = 'param';
    
    private DOMDocument $document;
    
    private DOMXPath $xpath;
    
    public function __construct(DOMDocument $document) {
        $this->document = $document;
        $this->xpath = DOMHelper::loadXPath($this->document, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_PHP);
    }
    
    public function getDocument(): DOMDocument {
        return $this->document;
    }
    
    public function lookupPageNode(string $path, DOMElement $contextNode = null): DOMElement {
        $path = str_pad($path, 1, '/');
        if ($contextNode === null or $path[0] === '/') {
            $contextNode = $this->getDomainNode();
        }
        $query = $this->path2expr($path, '[self::sfs:page | self::sfs:file]');
        $pageNode = $this->xpath->evaluate($query, $contextNode)->item(0);
        
        if (! $pageNode) {
            throw new PageNotFoundException($path);
        }
        
        if ($pageNode->hasAttribute('redirect')) {
            $redirectPath = $pageNode->getAttribute('redirect');
            
            $host = parse_url($redirectPath, PHP_URL_HOST);
            if ($host) {
                throw new PageRedirectionException($redirectPath);
            }
            
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
    
    public function getCurrentPageNode(): ?DOMElement {
        $currentNodes = $this->xpath->evaluate('//*[@current]');
        return $currentNodes->length ? $currentNodes->item(0) : null;
    }
    
    public function setCurrentPageNode(DOMElement $pageNode): void {
        if ($oldNode = $this->getCurrentPageNode()) {
            if ($oldNode === $pageNode) {
                return;
            }
            $oldNode->removeAttribute('current');
        }
        
        $pageNode->setAttribute('current', '1');
    }
    
    public function clearCurrentPageNode(): void {
        if ($oldNode = $this->getCurrentPageNode()) {
            $oldNode->removeAttribute('current');
        }
    }
    
    public function lookupAssetUrl(DOMElement $dataNode, array $args = []): FarahUrl {
        $vendorName = $this->findVendorName($dataNode);
        $moduleName = $this->findModuleName($dataNode);
        $args += $this->findParameters($dataNode);
        $ref = $dataNode->getAttribute('ref');
        
        return FarahUrl::createFromReference($ref, FarahUrl::createFromComponents(FarahUrlAuthority::createFromVendorAndModule($vendorName, $moduleName), null, FarahUrlArguments::createFromValueList($args)));
    }
    
    private function path2expr(string $path, string $filter = ''): string {
        $path = array_filter(explode('/', $path), 'strlen');
        $qry = [
            '.'
        ];
        foreach ($path as $folder) {
            $qry[] = '*[php:functionString("strtolower", @name) = "' . strtolower($folder) . '" or ancestor-or-self::*/@name = "*"]';
        }
        return implode('/', $qry);
    }
    
    // @deprecated
    private function findUri(DOMElement $pageNode): string {
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
    
    private function getDomainNode(): DOMElement {
        return $this->document->documentElement;
    }
    
    private function getDomainName(): string {
        return $this->getDomainNode()->getAttribute('name');
    }
    
    private function getProtocol(): string {
        return 'http'; // TODO: where to put this?
    }
    
    private function findModuleName(DOMElement $node): string {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@module][1]/@module)', $node);
    }
    
    private function findVendorName(DOMElement $node): string {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@vendor][1]/@vendor)', $node);
    }
    
    private function findParameters(DOMElement $node): array {
        $ret = [];
        foreach ($this->xpath->evaluate('ancestor-or-self::*/sfm:param', $node) as $paramNode) {
            $ret[$paramNode->getAttribute('name')] = $paramNode->getAttribute('value');
        }
        return $ret;
    }
    
    public function toRoutes(): array {
        $this->loadDocument();
        return [
            $this->getDomainName() => $this->toRoute($this->getDomainNode(), FarahUrl::createFromReference('farah://slothsoft@farah/'))
        ];
    }
    
    private function toRoute(DOMElement $pageNode, FarahUrl $contextUrl): array {
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
            case self::TAG_DOMAIN:
                $pageName = '/';
                break;
            case self::TAG_PAGE:
                $pageName = $pageNode->getAttribute('name') . '/';
            case self::TAG_FILE:
                $pageName = $pageNode->getAttribute('name');
                break;
        }
        
        $children = [];
        $params = [];
        foreach ($pageNode->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                switch ($node->localName) {
                    case self::TAG_PARAM:
                        $params[$node->getAttribute('name')] = $node->getAttribute('value');
                        break;
                    case self::TAG_PAGE:
                    case self::TAG_FILE:
                        $children[] = $this->toRoute($node, $contextUrl);
                        break;
                }
            }
        }
        $route = [
            'type' => 'literal',
            'options' => [
                'route' => $pageName,
                'defaults' => $params
            ],
            'child_routes' => $children
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


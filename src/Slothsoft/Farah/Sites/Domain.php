<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use DOMDocument;
use DOMElement;
use Slothsoft\Farah\Exception\EmptySitemapException;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\PageNotFoundException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Domain
{

    public static function getInstance(): self
    {
        static $instance;
        if ($instance === null) {
            $instance = new self(Kernel::getSitesFile()); // todo: move this somewhere else
        }
        return $instance;
    }

    const TAG_INCLUDE_PAGES = 'include-pages';

    const TAG_DOMAIN = 'domain';

    const TAG_PAGE = 'page';

    const TAG_PAGES = 'pages';

    private $file;

    private $document;

    private $domainNode;

    private $domainName;

    private $xpath;

    private function __construct(string $file)
    {
        $this->file = $file;
    }

    private function loadDocument()
    {
        if (! $this->document) {
            
            $this->document = DOMHelper::loadDocument($this->file);
            $this->xpath = DOMHelper::loadXPath($this->document, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_PHP);
            $this->domainNode = $this->document->documentElement;
            
            $node = $this->domainNode;
            if (! $node) {
                throw new EmptySitemapException($this->file);
            }
            
            assert($node->namespaceURI === DOMHelper::NS_FARAH_SITES and $node->localName === self::TAG_DOMAIN, sprintf('Root element of sitemap document must be <%s xmlns="%s">!', self::TAG_DOMAIN, DOMHelper::NS_FARAH_SITES));
            
            $this->domainName = $this->domainNode->getAttribute('name');
            
            $this->init();
        }
    }

    private function init()
    {
        // preload all include-pages elements
        log_execution_time(__FILE__, __LINE__);
        while ($nodeList = $this->document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, self::TAG_INCLUDE_PAGES) and $nodeList->length) {
            $dataNodeList = [];
            foreach ($nodeList as $node) {
                $dataNodeList[] = $node;
            }
            foreach ($dataNodeList as $dataNode) {
                $url = $this->lookupAssetUrl($dataNode);
                $result = FarahUrlResolver::resolveToResult($url);
                
                assert($result instanceof DOMWriterInterface, "To <sfs:include-pages> asset {$result->getId()}, it must implement DOMWriterInterface, not " . get_class($result) . ".");
                
                $node = $result->toElement($this->document);
                
                assert($node->namespaceURI === DOMHelper::NS_FARAH_SITES and $node->localName === self::TAG_PAGES, sprintf('To <sfs:include-pages> asset %s, its root element must be <%s xmlns="%s">!', $result->getId(), self::TAG_PAGES, DOMHelper::NS_FARAH_SITES));
                
                while ($node->hasChildNodes()) {
                    $dataNode->parentNode->insertBefore($node->firstChild, $dataNode);
                }
                
                $dataNode->parentNode->removeChild($dataNode);
            }
        }
        log_execution_time(__FILE__, __LINE__);
        $this->initDomainElement($this->domainNode);
        $nodeList = $this->document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, self::TAG_PAGE);
        foreach ($nodeList as $node) {
            $this->initPageElement($node);
        }
        log_execution_time(__FILE__, __LINE__);
    }

    private function initDomainElement(DOMElement $node)
    {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', '/');
        $node->setAttribute('url', "//{$this->getDomainName()}/");
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
        $node->setAttribute('url', "//{$this->getDomainName()}$uri");
    }

    public function getDocument(): DOMDocument
    {
        $this->loadDocument();
        
        return $this->document;
    }

    public function lookupPageNode(string $path, DOMElement $contextNode = null) : DOMElement
    {
        $this->loadDocument();
        
        $path = str_pad($path, 1, '/');
        if ($contextNode === null or $path[0] === '/') {
            $contextNode = $this->domainNode;
        }
        $query = $this->path2expr($path, 'sfs:' . self::TAG_PAGE);
        $pageNode = $this->xpath->evaluate($query, $contextNode)->item(0);
        
        if (!$pageNode) {
            throw new PageNotFoundException($path);
        }
        
        if ($pageNode->hasAttribute('redirect')) {
            $redirect = $ret->getAttribute('redirect');
            switch ($redirect) {
                case '..':
                    $pageNode = $pageNode->parentNode;
                    break;
                default:
                    $pageNode = $this->lookupPageNode($redirect, $pageNode);
                    break;
            }
        }
        
        return $pageNode;
    }

    public function lookupAssetUrl(DOMElement $dataNode, array $args = []): FarahUrl
    {
        $vendorName = $this->findVendorName($dataNode);
        $moduleName = $this->findModuleName($dataNode);
        $args += $this->findParameters($dataNode);
        $ref = $dataNode->getAttribute('ref');
        
        return FarahUrl::createFromReference($ref, FarahUrlAuthority::createFromVendorAndModule($vendorName, $moduleName), null, FarahUrlArguments::createFromValueList($args));
    }

    private function path2expr(string $path, string $elementName = '*') : string
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
}


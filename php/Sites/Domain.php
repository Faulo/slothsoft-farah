<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use DOMDocument;
use DOMElement;
use LengthException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Domain
{

    const TAG_INCLUDE_PAGES = 'include-pages';

    const TAG_DOMAIN = 'domain';

    const TAG_PAGE = 'page';

    const TAG_PAGES = 'pages';

    private $file;

    private $document;

    private $domainNode;

    private $xpath;

    public function __construct(string $file)
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
                throw new LengthException('Sitemap document appears to be empty.');
            }
            assert($node->namespaceURI === DOMHelper::NS_FARAH_SITES and $node->localName === self::TAG_DOMAIN, sprintf('Root element of sitemap document must be <%s xmlns="%s">!', self::TAG_DOMAIN, DOMHelper::NS_FARAH_SITES));
            
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
                $asset = AssetRepository::getInstance()->lookupAssetByUrl($url);
                
                assert($asset instanceof DOMWriterInterface, 'To <sfs:include-pages> asset {$asset->getId()}, it must implement DOMWriterInterface.');
                
                $node = $asset->toElement($this->document);
                
                assert($node->namespaceURI === DOMHelper::NS_FARAH_SITES and $node->localName === self::TAG_PAGES, sprintf('To <sfs:include-pages> asset %s, its root element must be <%s xmlns="%s">!', $asset->getId(), self::TAG_PAGES, DOMHelper::NS_FARAH_SITES));
                
                while ($node->hasChildNodes()) {
                    $dataNode->parentNode->insertBefore($node->firstChild, $dataNode);
                }
                
                $dataNode->parentNode->removeChild($dataNode);
            }
        }
        
        $this->initPageElement($this->domainNode);
        $nodeList = $this->document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, self::TAG_PAGE);
        foreach ($nodeList as $node) {
            $this->initPageElement($node);
        }
    }

    private function initPageElement(DOMElement $node)
    {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', $this->findUri($node));
        $node->setAttribute('url', $this->findUri($node, true));
    }

    public function getDocument(): DOMDocument
    {
        $this->loadDocument();
        
        return $this->document;
    }

    public function lookupPageNode(string $path, DOMElement $contextNode = null)
    {
        $this->loadDocument();
        
        if (! $contextNode) {
            $contextNode = $this->domainNode;
        }
        $ret = null;
        $redirected = false;
        $path = str_pad($path, 1, '/');
        if ($qry = $this->path2expr($path, 'sfs:' . self::TAG_PAGE)) {
            if ($path[0] === '/') {
                $contextNode = $this->domainNode;
            }
            $res = $this->xpath->evaluate($qry, $contextNode);
            // my_dump($qry);my_dump($res->length);
            if ($res->length) {
                $ret = $res->item(0);
            }
        }
        if ($ret and $ret->hasAttribute('redirect')) {
            $redirect = $ret->getAttribute('redirect');
            switch ($redirect) {
                case '..':
                    $ret = $ret->parentNode;
                    break;
                default:
                    $ret = $this->lookupPageNode($redirect, $ret);
                    break;
            }
            $redirected = true;
        }
        
        return $ret;
    }

    public function lookupAssetUrl(DOMElement $dataNode): FarahUrl
    {
        $vendorName = $this->findVendorName($dataNode);
        $moduleName = $this->findModuleName($dataNode);
        $args = $this->findParameters($dataNode);
        $ref = $dataNode->getAttribute('ref');
        
        return FarahUrl::createFromUri($ref, "farah://$vendorName@$moduleName", $args);
    }

    private function path2expr(string $path, string $elementName = '*')
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

    private function findUri(DOMElement $pageNode, bool $includeDomain = false): string
    {
        if ($pageNode->hasAttribute('ext')) {
            return $pageNode->getAttribute('ext');
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
        // TODO: get http from somewhere else maybe
        return $includeDomain ? 'http://' . $this->domainNode->getAttribute('name') . $ret : $ret;
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
        $ret = Kernel::getInstance()->getRequest()->input;
        foreach ($this->xpath->evaluate('ancestor-or-self::*/sfm:param', $node) as $paramNode) {
            $ret[$paramNode->getAttribute('name')] = $paramNode->getAttribute('value');
        }
        return $ret;
    }
}


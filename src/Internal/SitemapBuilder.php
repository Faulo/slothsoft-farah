<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use DOMElement;
use LogicException;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Exception\EmptySitemapException;
use Slothsoft\Farah\Exception\MalformedDocumentException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Http\WebScheme;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\MapResultBuilder;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StringWriterStreamBuilder;
use Slothsoft\Farah\Sites\Domain;

/**
 * Executable builder and DOM writer for Farah sitemap output.
 *
 * @author Daniel Schulz
 * @since 2025-09-18
 */
final class SitemapBuilder implements ExecutableBuilderStrategyInterface, DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private ?AssetInterface $asset = null;
    
    private ?DOMDocument $document = null;
    
    private string $domainName;
    
    private string $domainScheme = WebScheme::HTTP;
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $writer = new DOMWriterMemoryCache($this);
        $resultBuilder = new MapResultBuilder(new DOMWriterStreamBuilder($writer, 'sitemap'));
        $resultBuilder->addStreamBuilder(FarahUrlStreamIdentifier::createFromString('json'), new StringWriterStreamBuilder(new SitemapJsonBuilder($writer), 'sitemap', 'json'));
        return new ExecutableStrategies($resultBuilder);
    }
    
    public function toDocument(): DOMDocument {
        return $this->loadDocument();
    }
    
    private function loadDocument(): DOMDocument {
        try {
            $asset = Kernel::getCurrentSitemap();
            if ($this->asset === $asset && $this->document !== null) {
                return $this->document;
            }
            $document = $asset->lookupExecutable()
                ->lookupXmlResult()
                ->lookupDOMWriter()
                ->toDocument();
            $root = $document->documentElement;
            if ($root === null) {
                throw new EmptySitemapException((string) $asset->createUrl());
            }
            $this->initDocument($document, $root);
            $this->asset = $asset;
        } catch (ConfigurationRequiredException $e) {
            $document = new DOMDocument();
            $node = $document->createElementNS(DOMHelper::NS_FARAH_SITES, 'domain');
            $node->setAttribute('name', 'localhost');
            $node->setAttribute('version', '1.1');
            $node->setAttribute('title', $e->getMessage());
            $document->appendChild($node);
            $this->initDocument($document, $node);
            $this->asset = null;
        }
        return $this->document = $document;
    }
    
    private function initDocument(DOMDocument $document, DOMElement $domainNode): void {
        $this->domainName = $domainNode->getAttribute('name');
        $xpath = DOMHelper::loadXPath($document, DOMHelper::XPATH_SLOTHSOFT);
        
        $this->domainScheme = WebScheme::HTTP;
        try {
            $scheme = Kernel::getCurrentRequest()->getUri()->getScheme();
            if (WebScheme::isSupported($scheme)) {
                $this->domainScheme = WebScheme::normalize($scheme);
            }
        } catch (ConfigurationRequiredException) {
        }
        
        // preload all include-pages elements
        $domain = null;
        while ($dataNodeList = $xpath->query('//sfs:include-pages') and $dataNodeList->length) {
            $domain ??= new Domain($document);
            foreach ($dataNodeList as $dataNode) {
                if (! $dataNode instanceof DOMElement) {
                    throw new LogicException('Expected a sitemap include element.');
                }
                $url = $domain->lookupAssetUrl($dataNode);
                $result = Module::resolveToDOMWriter($url);
                $node = $result->toElement($document);
                $fragment = $document->createDocumentFragment();
                foreach ([
                             ...$node->childNodes
                         ] as $node) {
                    $fragment->appendChild($node);
                }
                $parent = $dataNode->parentNode;
                if ($parent !== null) {
                    $parent->replaceChild($fragment, $dataNode);
                }
            }
        }
        
        $this->initDomainElement($domainNode);
        
        $nodes = $xpath->query('//sfs:page | //sfs:file');
        if ($nodes === false) {
            throw new LogicException('Failed to query sitemap pages.');
        }
        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                throw new LogicException('Expected a sitemap element.');
            }
            $this->initPageElement($node);
        }
    }
    
    private function initDomainElement(DOMElement $node): void {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', '/');
        $node->setAttribute('url', "$this->domainScheme://$this->domainName/");
    }
    
    private function initPageElement(DOMElement $node): void {
        $name = $node->getAttribute('name');
        if ($node->hasAttribute('ext')) {
            $uri = $node->getAttribute('ext');
        } else {
            $parent = $node->parentNode;
            if (! $parent instanceof DOMElement) {
                throw new MalformedDocumentException($node->baseURI ?? '');
            }
            $parentUri = $parent->getAttribute('uri');
            $uri = match ($node->localName) {
                Domain::TAG_FILE => $parentUri . $name,
                default => $parentUri . $name . '/',
            };
        }
        
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $name);
        }
        $node->setAttribute('uri', $uri);
        $node->setAttribute('url', "$this->domainScheme://$this->domainName$uri");
    }
}

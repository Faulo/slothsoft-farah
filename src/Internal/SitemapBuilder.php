<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use DOMElement;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Exception\EmptySitemapException;
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
    
    public const PARAM_SCHEME = 'scheme';

    private ?AssetInterface $asset = null;
    
    private ?DOMDocument $document = null;
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $scheme = WebScheme::normalize((string) $args->get(self::PARAM_SCHEME, WebScheme::HTTP));
        $writer = new DOMWriterFromDocumentDelegate(fn(): DOMDocument => $this->createDocument($scheme));
        $writer = new DOMWriterMemoryCache($writer);
        $resultBuilder = new MapResultBuilder(new DOMWriterStreamBuilder($writer, 'sitemap'));
        $resultBuilder->addStreamBuilder(FarahUrlStreamIdentifier::createFromString('json'), new StringWriterStreamBuilder(new SitemapJsonBuilder($writer), 'sitemap', 'json'));
        return new ExecutableStrategies($resultBuilder);
    }
    
    public function toDocument(): DOMDocument {
        try {
            $scheme = Kernel::getCurrentRequest()->getUri()->getScheme();
            $scheme = WebScheme::isSupported($scheme) ? WebScheme::normalize($scheme) : WebScheme::HTTP;
        } catch (ConfigurationRequiredException) {
            $scheme = WebScheme::HTTP;
        }
        return $this->createDocument($scheme);
    }
    
    private function loadDocument(): void {
        try {
            $asset = Kernel::getCurrentSitemap();
            
            if ($this->asset !== $asset) {
                $this->asset = $asset;
                $this->document = $this->asset->lookupExecutable()
                    ->lookupXmlResult()
                    ->lookupDOMWriter()
                    ->toDocument();
                
                if (! $this->document->documentElement) {
                    throw new EmptySitemapException((string) $this->asset->createUrl());
                }
                
            }
        } catch (ConfigurationRequiredException $e) {
            $this->document = new DOMDocument();
            $node = $this->document->createElementNS(DOMHelper::NS_FARAH_SITES, 'domain');
            $node->setAttribute('name', 'localhost');
            $node->setAttribute('version', '1.1');
            $node->setAttribute('title', $e->getMessage());
            $this->document->appendChild($node);
        }
    }
    
    private function createDocument(string $scheme): DOMDocument {
        $this->loadDocument();
        $document = clone $this->document;
        $domainNode = $document->documentElement;
        $domainName = $domainNode->getAttribute('name');
        $xpath = DOMHelper::loadXPath($document, DOMHelper::XPATH_SLOTHSOFT);
        
        // preload all include-pages elements
        $domain = null;
        while ($dataNodeList = $xpath->query('//sfs:include-pages') and $dataNodeList->length) {
            $domain ??= new Domain($document);
            foreach ($dataNodeList as $dataNode) {
                $url = $domain->lookupAssetUrl($dataNode);
                $result = Module::resolveToDOMWriter($url);
                $node = $result->toElement($document);
                $fragment = $document->createDocumentFragment();
                foreach ([
                             ...$node->childNodes
                         ] as $node) {
                    $fragment->appendChild($node);
                }
                $dataNode->parentNode->replaceChild($fragment, $dataNode);
            }
        }
        
        $this->initDomainElement($domainNode, $scheme, $domainName);
        
        foreach ($xpath->query('//sfs:page | //sfs:file') as $node) {
            $this->initPageElement($node, $scheme, $domainName);
        }
        return $document;
    }
    
    private function initDomainElement(DOMElement $node, string $scheme, string $domainName): void {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', '/');
        $node->setAttribute('url', "$scheme://$domainName/");
    }
    
    private function initPageElement(DOMElement $node, string $scheme, string $domainName): void {
        $name = $node->getAttribute('name');
        if ($node->hasAttribute('ext')) {
            $uri = $node->getAttribute('ext');
        } else {
            $parentUri = $node->parentNode->getAttribute('uri');
            $uri = match ($node->localName) {
                Domain::TAG_FILE => $parentUri . $name,
                default => $parentUri . $name . '/',
            };
        }
        
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $name);
        }
        $node->setAttribute('uri', $uri);
        $node->setAttribute('url', "$scheme://$domainName$uri");
    }
}

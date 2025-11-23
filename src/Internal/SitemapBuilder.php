<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\EmptySitemapException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\MapResultBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StringWriterStreamBuilder;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SitemapBuilder implements ExecutableBuilderStrategyInterface, DOMWriterInterface, StringWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private ?AssetInterface $asset = null;
    
    private ?DOMDocument $document = null;
    
    private DOMXPath $xpath;
    
    private DOMElement $domainNode;
    
    private string $domainName;
    
    private string $domainProtocol = 'http';
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        if (isset($_SERVER['SERVER_PROTOCOL']) and $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')))) {
            $this->domainProtocol = $protocol;
        }
        
        $resultBuilder = new MapResultBuilder(new DOMWriterStreamBuilder($this, 'sitemap'));
        $resultBuilder->addStreamBuilder(FarahUrlStreamIdentifier::createFromString('json'), new StringWriterStreamBuilder($this, 'sitemap', 'json'));
        return new ExecutableStrategies($resultBuilder);
    }
    
    public function toDocument(): DOMDocument {
        $this->loadDocument();
        
        return $this->document;
    }
    
    public function toString(): string {
        $this->loadDocument();
        
        $data = [];
        foreach ($this->xpath->query('//*[@uri]') as $node) {
            $data[] = $node->getAttribute('uri');
        }
        
        return json_encode($data);
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
                
                $this->initDocument();
            }
        } catch (ConfigurationRequiredException $e) {
            $this->document = new DOMDocument();
            $node = $this->document->createElementNS(DOMHelper::NS_FARAH_SITES, 'domain');
            $node->setAttribute('name', 'localhost');
            $node->setAttribute('version', '1.1');
            $node->setAttribute('title', $e->getMessage());
            $this->document->appendChild($node);
            $this->initDocument();
        }
    }
    
    private function initDocument(): void {
        $this->domainNode = $this->document->documentElement;
        $this->domainName = $this->domainNode->getAttribute('name');
        $this->xpath = DOMHelper::loadXPath($this->document, DOMHelper::XPATH_SLOTHSOFT);
        
        // preload all include-pages elements
        $domain = null;
        while ($dataNodeList = $this->xpath->query('//sfs:include-pages') and $dataNodeList->length) {
            $domain ??= new Domain($this->document);
            foreach ($dataNodeList as $dataNode) {
                $url = $domain->lookupAssetUrl($dataNode);
                $result = Module::resolveToResult($url);
                $node = $result->toElement($this->document);
                $fragment = $this->document->createDocumentFragment();
                foreach ([
                    ...$node->childNodes
                ] as $node) {
                    $fragment->appendChild($node);
                }
                $dataNode->parentNode->replaceChild($fragment, $dataNode);
            }
        }
        
        $this->initDomainElement($this->domainNode);
        
        foreach ($this->xpath->query('//sfs:page | //sfs:file') as $node) {
            $this->initPageElement($node);
        }
    }
    
    private function initDomainElement(DOMElement $node): void {
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $node->getAttribute('name'));
        }
        $node->setAttribute('uri', '/');
        $node->setAttribute('url', "{$this->domainProtocol}://{$this->domainName}/");
    }
    
    private function initPageElement(DOMElement $node): void {
        $name = $node->getAttribute('name');
        if ($node->hasAttribute('ext')) {
            $uri = $node->getAttribute('ext');
        } else {
            $parentUri = $node->parentNode->getAttribute('uri');
            switch ($node->localName) {
                case Domain::TAG_PAGE:
                    $uri = $parentUri . $name . '/';
                    break;
                case Domain::TAG_FILE:
                    $uri = $parentUri . $name;
                    break;
            }
        }
        
        if (! $node->hasAttribute('title')) {
            $node->setAttribute('title', $name);
        }
        $node->setAttribute('uri', $uri);
        $node->setAttribute('url', "{$this->domainProtocol}://{$this->domainName}$uri");
    }
}
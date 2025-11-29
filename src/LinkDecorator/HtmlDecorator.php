<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HtmlDecorator implements LinkDecoratorInterface {
    
    private DOMDocument $targetDocument;
    
    private DOMElement $rootNode;
    
    public function setTarget(DOMDocument $document): void {
        $this->targetDocument = $document;
        
        $this->rootNode = $document->getElementsByTagNameNS(DOMHelper::NS_HTML, 'head')->item(0) ?? $document->documentElement;
    }
    
    public function linkStylesheets(FarahUrl ...$stylesheets): void {
        foreach ($stylesheets as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'link');
            $node->setAttribute('href', $href);
            $node->setAttribute('rel', 'stylesheet');
            $node->setAttribute('type', 'text/css');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkScripts(FarahUrl ...$scripts): void {
        foreach ($scripts as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'script');
            $node->setAttribute('src', $href);
            $node->setAttribute('type', 'application/javascript');
            $node->setAttribute('defer', 'defer');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkModules(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'script');
            $node->setAttribute('src', $href);
            $node->setAttribute('type', 'module');
            $node->setAttribute('async', 'async'); // Chromium only supports async modules https://issues.chromium.org/issues/40518469#comment28
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkContents(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $base = (string) $url->withStreamIdentifier(FarahUrlStreamIdentifier::createEmpty())->withQueryArguments(FarahUrlArguments::createEmpty());
            $href = (string) $url;
            
            $contentNode = Module::resolveToDOMWriter($url)->toElement($this->targetDocument);
            
            if ($contentNode->namespaceURI === null) {
                $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'html:template');
                $node->setAttribute('xmlns', '');
            } else {
                $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'template');
            }
            
            $node->setAttributeNS(DOMHelper::NS_XML, 'base', $base);
            $node->setAttribute('data-url', $href);
            $node->appendChild($contentNode);
            
            $this->rootNode->appendChild($node);
        }
    }
}


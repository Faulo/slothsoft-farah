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
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkContents(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $base = (string) $url->withStreamIdentifier(FarahUrlStreamIdentifier::createEmpty())->withQueryArguments(FarahUrlArguments::createEmpty());
            $href = (string) $url;
            
            $contentNode = Module::resolveToDOMWriter($url)->toElement($this->targetDocument);
            
            if ($contentNode->namespaceURI === null) {
                $fragment = $this->targetDocument->createDocumentFragment();
                $fragment->appendXML('<html:template xmlns:html="http://www.w3.org/1999/xhtml" xmlns="">' . $this->targetDocument->saveXML($contentNode) . '</html:template>');
                $node = $fragment->firstChild;
            } else {
                $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'template');
                $node->appendChild($contentNode);
            }
            
            $node->setAttributeNS(DOMHelper::NS_XML, 'base', $base);
            $node->setAttribute('data-url', $href);
            
            $this->rootNode->appendChild($node);
        }
    }
}


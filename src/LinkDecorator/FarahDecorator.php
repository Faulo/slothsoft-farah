<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Manifest\Manifest;
use DOMDocument;
use DOMElement;
use Slothsoft\Core\DOMHelper;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahDecorator implements LinkDecoratorInterface {
    
    private string $namespace = DOMHelper::NS_FARAH_MODULE;
    
    private DOMDocument $targetDocument;
    
    private DOMElement $rootNode;
    
    public function setTarget(DOMDocument $document): void {
        $this->targetDocument = $document;
        
        $this->rootNode = $document->documentElement;
    }
    
    public function linkStylesheets(FarahUrl ...$stylesheets): void {
        foreach ($stylesheets as $url) {
            $href = (string) $url;
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_STYLESHEET);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkScripts(FarahUrl ...$scripts): void {
        foreach ($scripts as $url) {
            $href = (string) $url;
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_SCRIPT);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkModules(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $href = (string) $url;
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_MODULE);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkContents(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $href = (string) $url;
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_CONTENT);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
}


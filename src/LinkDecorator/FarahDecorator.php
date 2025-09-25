<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Manifest\Manifest;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahDecorator implements LinkDecoratorInterface {
    
    private $namespace;
    
    private $targetDocument;
    
    private $rootNode;
    
    public function setNamespace(string $namespace) {
        $this->namespace = $namespace;
    }
    
    public function setTarget(DOMDocument $document) {
        $this->targetDocument = $document;
        
        $this->rootNode = $document->documentElement;
    }
    
    public function linkStylesheets(FarahUrl ...$stylesheets) {
        foreach ($stylesheets as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_STYLESHEET);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkScripts(FarahUrl ...$scripts) {
        foreach ($scripts as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_SCRIPT);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkModules(FarahUrl ...$modules) {
        foreach ($modules as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, Manifest::TAG_LINK_MODULE);
            $node->setAttribute(Manifest::ATTR_REFERENCE, $href);
            $this->rootNode->appendChild($node);
        }
    }
}


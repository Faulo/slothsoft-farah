<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SvgDecorator implements LinkDecoratorInterface {
    
    private string $namespace = DOMHelper::NS_SVG;
    
    private DOMDocument $targetDocument;
    
    private DOMElement $rootNode;
    
    private bool $useProcessingInstruction = false;
    
    public function setTarget(DOMDocument $document): void {
        $this->targetDocument = $document;
        
        $this->rootNode = $document->getElementsByTagNameNS($this->namespace, 'defs')->item(0) ?? $document->documentElement;
    }
    
    public function linkStylesheets(FarahUrl ...$stylesheets): void {
        foreach ($stylesheets as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            if ($this->useProcessingInstruction) {
                $node = $this->targetDocument->createProcessingInstruction('xml-stylesheet', sprintf('type="text/css" href="%s"', $href));
                $this->targetDocument->insertBefore($node, $this->targetDocument->firstChild);
            } else {
                $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'link');
                $node->setAttribute('href', $href);
                $node->setAttribute('rel', 'stylesheet');
                $node->setAttribute('type', 'text/css');
                $this->rootNode->appendChild($node);
            }
        }
    }
    
    public function linkScripts(FarahUrl ...$scripts): void {
        foreach ($scripts as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttribute('href', $href);
            $node->setAttribute('type', 'application/javascript');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkModules(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $href = str_replace('farah://', '/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttribute('href', $href);
            $node->setAttribute('type', 'module');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkContents(FarahUrl ...$modules): void {
        foreach ($modules as $url) {
            $href = (string) $url;
            
            $node = $this->targetDocument->createElementNS(DOMHelper::NS_HTML, 'template');
            $node->setAttribute('data-url', $href);
            $node->appendChild(Module::resolveToDOMWriter($url)->toElement($this->targetDocument));
            $this->rootNode->appendChild($node);
        }
    }
}


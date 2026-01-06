<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Ds\Set;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

final class DecoratedDOMWriter implements DOMWriterInterface {
    
    private DOMWriterInterface $source;
    
    private Set $stylesheets;
    
    private Set $scripts;
    
    private Set $modules;
    
    private Set $contents;
    
    public function __construct(DOMWriterInterface $source, Set $stylesheets, Set $scripts, Set $modules, Set $contents) {
        $this->source = $source;
        $this->stylesheets = $stylesheets;
        $this->scripts = $scripts;
        $this->modules = $modules;
        $this->contents = $contents;
    }
    
    private function hasLinks(): bool {
        return ! $this->stylesheets->isEmpty() or ! $this->scripts->isEmpty() or ! $this->modules->isEmpty() or ! $this->contents->isEmpty();
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $element = $this->source->toElement($targetDoc);
        
        if ($this->hasLinks()) {
            $decorator = DecoratorFactory::createForElement($element);
            $decorator->linkStylesheets(...$this->stylesheets);
            $decorator->linkScripts(...$this->scripts);
            $decorator->linkModules(...$this->modules);
            $decorator->linkContents(...$this->contents);
        }
        
        return $element;
    }
    
    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        
        if ($this->hasLinks()) {
            $decorator = DecoratorFactory::createForDocument($document);
            $decorator->linkStylesheets(...$this->stylesheets);
            $decorator->linkScripts(...$this->scripts);
            $decorator->linkModules(...$this->modules);
            $decorator->linkContents(...$this->contents);
        }
        
        return $document;
    }
}


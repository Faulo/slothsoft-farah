<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Ds\Set;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class DecoratedDOMWriter implements DOMWriterInterface {
    
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
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $element = $this->source->toElement($targetDoc);
        $decorator = DecoratorFactory::createForElement($element);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        $decorator->linkModules(...$this->modules);
        return $element;
    }
    
    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        $decorator = DecoratorFactory::createForDocument($document);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        $decorator->linkModules(...$this->modules);
        return $document;
    }
}


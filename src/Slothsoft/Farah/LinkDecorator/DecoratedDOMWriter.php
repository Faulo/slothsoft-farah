<?php
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class DecoratedDOMWriter implements DOMWriterInterface
{
    private $source;
    private $stylesheets;
    private $scripts;
    public function __construct(DOMWriterInterface $source, array $stylesheets, array $scripts) {
        $this->source = $source;
        $this->stylesheets = $stylesheets;
        $this->scripts = $scripts;
    }
    
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        $element = $this->source->toElement($targetDoc);
        $decorator = DecoratorFactory::createForElement($element);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        return $element;
    }

    public function toDocument() : DOMDocument
    {
        $document = $this->source->toDocument();
        $decorator = DecoratorFactory::createForDocument($document);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        return $document;
    }
}


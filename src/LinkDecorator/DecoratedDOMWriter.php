<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class DecoratedDOMWriter implements DOMWriterInterface {

    private $source;

    /**
     *
     * @var iterable
     */
    private $stylesheets;

    /**
     *
     * @var iterable
     */
    private $scripts;

    /**
     *
     * @var iterable
     */
    private $modules;

    public function __construct(DOMWriterInterface $source, iterable $stylesheets, iterable $scripts, iterable $modules) {
        $this->source = $source;
        $this->stylesheets = $stylesheets;
        $this->scripts = $scripts;
        $this->modules = $modules;
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


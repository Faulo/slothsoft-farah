<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class DecoratedDOMWriter implements DOMWriterInterface
{

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

    public function __construct(DOMWriterInterface $source, iterable $stylesheets, iterable $scripts)
    {
        $this->source = $source;
        $this->stylesheets = $stylesheets;
        $this->scripts = $scripts;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $this->source->toElement($targetDoc);
        $decorator = DecoratorFactory::createForElement($element);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        return $element;
    }

    public function toDocument(): DOMDocument
    {
        $document = $this->source->toDocument();
        $decorator = DecoratorFactory::createForDocument($document);
        $decorator->linkStylesheets(...$this->stylesheets);
        $decorator->linkScripts(...$this->scripts);
        return $document;
    }
}


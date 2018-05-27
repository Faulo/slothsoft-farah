<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class ElementClosureDOMWriter implements DOMWriterInterface
{
    use DOMWriterDocumentFromElementTrait;

    /**
     *
     * @var callable
     */
    private $closure;

    public function __construct(callable $closure)
    {
        $this->closure = $closure;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return ($this->closure)($targetDoc);
    }
}


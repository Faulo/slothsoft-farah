<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;
use DOMElement;

class DOMWriterExecutable extends ExecutableDOMWriterBase
{
    private $writer;
    public function __construct(DOMWriterInterface $writer) {
        $this->writer = $writer;
    }
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        return $this->writer->toElement($targetDoc);
    }

    public function toDocument() : DOMDocument
    {
        return $this->writer->toDocument();
    }

}


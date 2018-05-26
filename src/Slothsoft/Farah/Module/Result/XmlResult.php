<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Exception\MalformedDocumentException;
use DOMDocument;

class XmlResult extends Result implements DOMWriterInterface
{
    use DOMWriterElementFromDocumentTrait;

    public function toDocument(): DOMDocument
    {
        $stream = $this->lookupStream();
        $doc = new DOMDocument();
        $success = $doc->loadXML($stream->getContents());
        if (! $success) {
            throw new MalformedDocumentException((string) $this->createUrl());
        }
        return $doc;
    }
}


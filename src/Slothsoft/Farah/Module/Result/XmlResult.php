<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Exception\MalformedDocumentException;
use DOMDocument;

class XmlResult extends Result implements ResultInterfacePlusXml
{
    use DOMWriterElementFromDocumentTrait;

    /**
     *
     * @var DOMDocument
     */
    private $document;

    public function toDocument(): DOMDocument
    {
        if ($this->document === null) {
            $this->document = new DOMDocument();
            $stream = $this->lookupStream();
            $success = $this->document->loadXML($stream->getContents());
            if (! $success) {
                throw new MalformedDocumentException((string) $this->createUrl());
            }
        }
        return $this->document;
    }
}


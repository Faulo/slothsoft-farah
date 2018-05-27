<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;

class Base64FileResultBuilder extends AbstractFileResultBuilder
{
    use DOMWriterDocumentFromElementTrait;

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $targetDoc->createElement('base64');
        $element->textContent = base64_encode($this->file->getContents());
        return $element;
    }
}


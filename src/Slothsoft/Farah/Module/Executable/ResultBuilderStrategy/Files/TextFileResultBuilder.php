<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;

class TextFileResultBuilder extends AbstractFileResultBuilder
{
    use DOMWriterDocumentFromElementTrait;

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $targetDoc->createElement('text');
        $element->textContent = $this->file->getContents();
        return $element;
    }
}

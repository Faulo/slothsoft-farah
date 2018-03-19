<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class BinaryFileResult extends FileResult
{
    use DOMWriterDocumentFromElementTrait;
    
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $targetDoc->createElement(basename(__CLASS__));
        $element->textContent = base64_encode($this->toString());
        return $element;
    }
}
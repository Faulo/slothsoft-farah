<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class NullResult extends GenericResult
{
    use FileWriterFromDOMTrait;
    use DOMWriterDocumentFromElementTrait;

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->createElement('null');
    }

    public function exists(): bool
    {
        return false;
    }
}


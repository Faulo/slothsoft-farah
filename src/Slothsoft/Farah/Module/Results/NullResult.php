<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\AssetUses\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\AssetUses\FileWriterFromDOMTrait;
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
    
    public function toElement(DOMDocument $targetDoc) : DOMElement {
        return $targetDoc->createElement('null');
    }
    public function exists() : bool {
        return false;
    }
}


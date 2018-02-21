<?php
namespace Slothsoft\Farah\Module\AssetUses;

use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
trait DOMWriterDocumentFromElementTrait {
    public function toDocument(): DOMDocument
    {
        $targetDoc = new DOMDocument();
        $targetDoc->appendChild($this->toElement($targetDoc));
        return $targetDoc;
    }
}


<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetUses;

use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
trait DOMWriterFromDefinitionTrait
{

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $this->toDefinitionElement($targetDoc);
    }

    public function toDocument(): DOMDocument
    {
        $targetDoc = new DOMDocument();
        $targetDoc->appendChild($this->toElement($targetDoc));
        return $targetDoc;
    }
}


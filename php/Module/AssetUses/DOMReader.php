<?php
namespace Slothsoft\Farah\Module\AssetUses;

use DOMDocument;
use DOMDocumentFragment;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface DOMReader
{
    public function fromDocument(DOMDocument $sourceDoc);
    
    public function fromDocumentFragment(DOMDocumentFragment $sourceFragment);
}


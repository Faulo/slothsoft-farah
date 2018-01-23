<?php
namespace Slothsoft\Farah\Module\AssetUses;

use DOMDocument;
use DOMDocumentFragment;
use DOMNode;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface DOMWriter
{
    public function toNode(DOMDocument $targetDoc = null) : DOMNode;
}


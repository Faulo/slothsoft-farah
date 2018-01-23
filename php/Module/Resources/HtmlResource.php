<?php
namespace Slothsoft\Farah\Module\Resources;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Module\Resource;
use Slothsoft\Farah\Module\AssetUses\DOMWriter;
use DOMDocument;
use DOMNode;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HtmlResource extends Resource implements DOMWriter
{
    
    public function toNode(DOMDocument $targetDoc = null) : DOMNode
    {
        $sourceDoc = DOMHelper::loadDocument($this->getRealPath(), true);
        
        return $targetDoc
        ? $targetDoc->importNode($sourceDoc->documentElement, true)
        : $sourceDoc;
    }
    
}
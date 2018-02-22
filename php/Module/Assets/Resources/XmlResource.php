<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Module\AssetUses\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\Assets\Resource;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class XmlResource extends Resource implements DOMWriterInterface
{
    use DOMWriterElementFromDocumentTrait;

    public function toDocument(): DOMDocument
    {
        return DOMHelper::loadDocument($this->getRealPath(), false);
    }
}


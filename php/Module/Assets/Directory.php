<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\AssetUses\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Directory extends GenericAsset implements DOMWriterInterface
{
    use DOMWriterDocumentFromElementTrait;

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $definition = $this->getDefinition();
        
        $node = $this->toDefinitionElement($targetDoc);
        foreach ($definition->getPathResolver()->getPathMap() as $path => $childDefinition) {
            if ($path !== '/') {
                $asset = AssetRepository::getInstance()->lookupAssetByUrl($childDefinition->toUrl($this->getArguments()));
                $node->appendChild($asset->toDefinitionElement($targetDoc));
            }
        }
        return $node;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\AssetReferenceTrait;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseDocumentInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseDocumentMeta extends MetaImplementation implements UseDocumentInstruction
{
    use AssetReferenceTrait {
        getReferencedAsset as public getReferencedDocumentAsset;
    }

    public function getReferencedDocumentAlias(): string
    {
        return $this->getElementAttribute(Module::ATTR_ALIAS, $this->getReferencedDocumentAsset()
            ->getName());
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\AssetReferenceTrait;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\ImportInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ImportMeta extends MetaImplementation implements ImportInstruction
{
    use AssetReferenceTrait;

    public function getImportNodes(): array
    {
        return $this->getReferencedAsset()->getChildren();
    }
}


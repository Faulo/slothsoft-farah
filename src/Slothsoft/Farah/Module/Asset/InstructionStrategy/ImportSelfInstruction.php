<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class ImportSelfInstruction extends NullInstruction
{
    use InstructionFromReferenceAttributeTrait {
        getReferencedAsset as public getImportAsset;
    }

    public function isImportSelf(AssetInterface $context): bool
    {
        return true;
    }
}
<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class UseManifestInstruction extends NullInstruction
{
    use InstructionFromReferenceAttributeTrait {
        getReferencedAsset as public getUseAsset;
    }

    public function isUseManifest(AssetInterface $context): bool
    {
        return true;
    }
}


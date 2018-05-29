<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class UseTemplateInstruction extends NullInstruction
{
    use InstructionFromReferenceAttributeTrait {
        getReferencedAsset as public getUseAsset;
    }

    public function isUseTemplate(AssetInterface $context): bool
    {
        return true;
    }
}
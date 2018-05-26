<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class ImportChildrenInstruction extends NullInstruction
{
    use InstructionFromReferenceAttributeTrait;

    public function isImportChildren(AssetInterface $context): bool
    {
        return true;
    }
}

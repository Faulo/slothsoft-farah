<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class UseDocumentInstruction extends NullInstruction {

    public function isUseDocument(AssetInterface $context): bool {
        return true;
    }
}


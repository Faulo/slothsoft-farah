<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class LinkContentInstruction extends NullInstruction {
    
    public function isLinkContent(AssetInterface $context): bool {
        return true;
    }
}


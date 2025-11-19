<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class LinkDictionaryInstruction extends NullInstruction {
    
    public function isLinkDictionary(AssetInterface $context): bool {
        return true;
    }
}


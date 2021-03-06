<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class LinkModuleInstruction extends NullInstruction {

    public function isLinkModule(AssetInterface $context): bool {
        return true;
    }
}


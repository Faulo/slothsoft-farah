<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class UseTemplateInstruction extends NullInstruction {

    public function isUseTemplate(AssetInterface $context): bool {
        return true;
    }
}
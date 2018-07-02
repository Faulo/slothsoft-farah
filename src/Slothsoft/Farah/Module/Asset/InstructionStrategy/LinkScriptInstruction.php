<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class LinkScriptInstruction extends NullInstruction
{
    public function isLinkScript(AssetInterface $context): bool
    {
        return true;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class LinkStylesheetInstruction extends NullInstruction
{

    public function isLinkStylesheet(AssetInterface $context): bool
    {
        return true;
    }
}


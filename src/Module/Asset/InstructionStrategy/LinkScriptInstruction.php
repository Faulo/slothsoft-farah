<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Instruction strategy for link script manifest instructions.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class LinkScriptInstruction extends NullInstruction {
    
    public function isLinkScript(AssetInterface $context): bool {
        return true;
    }
}


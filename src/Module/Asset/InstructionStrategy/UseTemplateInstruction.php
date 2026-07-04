<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Instruction strategy for use template manifest instructions.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class UseTemplateInstruction extends NullInstruction {
    
    public function isUseTemplate(AssetInterface $context): bool {
        return true;
    }
}
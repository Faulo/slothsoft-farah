<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Instruction strategy for parameter supplier manifest instructions.
 *
 * @author Daniel Schulz
 * @since 2018-06-23
 */
final class ParameterSupplierInstruction extends NullInstruction {
    
    public function isParameterSupplier(AssetInterface $context): bool {
        return true;
    }
}
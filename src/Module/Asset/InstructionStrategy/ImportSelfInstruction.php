<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Instruction strategy for import self manifest instructions.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ImportSelfInstruction extends NullInstruction {
    
    public function isImportSelf(AssetInterface $context): bool {
        return true;
    }
}
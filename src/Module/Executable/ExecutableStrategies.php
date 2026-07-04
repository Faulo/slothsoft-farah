<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\InstructionBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\NullInstructionBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;

/**
 * Strategy bundle used when building executable results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ExecutableStrategies {
    
    public ResultBuilderStrategyInterface $resultBuilder;
    
    public InstructionBuilderStrategyInterface $instructionBuilder;
    
    public function __construct(ResultBuilderStrategyInterface $resultBuilder, ?InstructionBuilderStrategyInterface $instructionBuilder = null) {
        $this->resultBuilder = $resultBuilder;
        $this->instructionBuilder = $instructionBuilder ?? new NullInstructionBuilder();
    }
}


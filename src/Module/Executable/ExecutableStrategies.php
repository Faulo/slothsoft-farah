<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\InstructionBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\NullInstructionBuilder;

class ExecutableStrategies {
    
    public ResultBuilderStrategyInterface $resultBuilder;
    
    public InstructionBuilderStrategyInterface $instructionBuilder;
    
    public function __construct(ResultBuilderStrategyInterface $resultBuilder, ?InstructionBuilderStrategyInterface $instructionBuilder = null) {
        $this->resultBuilder = $resultBuilder;
        $this->instructionBuilder = $instructionBuilder ?? new NullInstructionBuilder();
    }
}


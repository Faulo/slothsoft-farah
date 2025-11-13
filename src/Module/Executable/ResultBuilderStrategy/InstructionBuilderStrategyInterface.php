<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;

interface InstructionBuilderStrategyInterface {
    
    public function buildUseInstructions(AssetInterface $context, FarahUrlArguments $args): UseInstructionCollection;
    
    public function buildLinkInstructions(AssetInterface $context, FarahUrlArguments $args): LinkInstructionCollection;
}


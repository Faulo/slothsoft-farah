<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;

class NullInstructionBuilder implements InstructionBuilderStrategyInterface {
    
    public function buildUseInstructions(AssetInterface $context, FarahUrlArguments $args): UseInstructionCollection {
        return new UseInstructionCollection($context->createUrl($args));
    }
    
    public function buildLinkInstructions(AssetInterface $context, FarahUrlArguments $args): LinkInstructionCollection {
        return new LinkInstructionCollection();
    }
}


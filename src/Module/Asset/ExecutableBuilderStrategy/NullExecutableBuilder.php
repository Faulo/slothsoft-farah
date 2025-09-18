<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\NullResultBuilder;

class NullExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies(new NullResultBuilder());
    }
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\MapResultBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/** Installs a per-process test result without mocking request processing. */
final class RangeResponseBuilder implements ExecutableBuilderStrategyInterface {
    
    public static StreamBuilderStrategyInterface $streamBuilder;
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies(new MapResultBuilder(self::$streamBuilder));
    }
}

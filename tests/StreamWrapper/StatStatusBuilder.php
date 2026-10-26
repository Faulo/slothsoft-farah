<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;

/** Exercises statUrl's policy for an HTTP status with no result to inspect. */
final class StatStatusBuilder implements ExecutableBuilderStrategyInterface, ResultBuilderStrategyInterface {
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies($this);
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return false;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        throw new HttpStatusException('', (int) $context->getUrlArguments()->get('status'));
    }
}

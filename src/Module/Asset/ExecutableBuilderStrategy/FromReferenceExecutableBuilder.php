<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyInstructionBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyResultBuilder;

class FromReferenceExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $url = $context->createRealUrl($args);
        $executable = Module::resolveToExecutable($url);
        $resultBuilder = new ProxyResultBuilder($executable, $url->getStreamIdentifier());
        $instructionBuilder = new ProxyInstructionBuilder($executable);
        return new ExecutableStrategies($resultBuilder, $instructionBuilder);
    }
}


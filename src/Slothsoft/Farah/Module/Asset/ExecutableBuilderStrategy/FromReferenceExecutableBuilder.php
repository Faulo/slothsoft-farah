<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyResultBuilder;

class FromReferenceExecutableBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $asset = $context->getReferencedInstructionAsset();
        if ($asset === $context) {
            throw new \RuntimeException("Cannot build executable, circular dependency in {$context->createUrl()}");
        }
        $resultBuilder = new ProxyResultBuilder($asset->lookupExecutable($args));
        return new ExecutableStrategies($resultBuilder);
    }
}


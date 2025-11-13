<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FromManifestInstructionBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\TransformationResultBuilder;

class FromManifestExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $resultBuilder = new TransformationResultBuilder(count(Dictionary::getSupportedLanguages()) !== 0);
        $instructionBuilder = new FromManifestInstructionBuilder();
        return new ExecutableStrategies($resultBuilder, $instructionBuilder);
    }
}


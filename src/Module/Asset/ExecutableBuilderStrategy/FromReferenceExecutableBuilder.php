<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyResultBuilder;
use Slothsoft\Farah\Module\Manifest\Manifest;

class FromReferenceExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $ref = $context->getManifestElement()->getAttribute(Manifest::ATTR_REFERENCE);
        $url = FarahUrl::createFromReference($ref, $context->createUrl())->withAdditionalQueryArguments($args);
        $resultBuilder = new ProxyResultBuilder(Module::resolveToExecutable($url), $url->getStreamIdentifier());
        return new ExecutableStrategies($resultBuilder);
    }
}


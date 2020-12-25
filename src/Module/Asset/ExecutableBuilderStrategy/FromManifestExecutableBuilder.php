<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\TransformationResultBuilder;

class FromManifestExecutableBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $rootAsset = $context;
        $getUseInstructions = function () use ($rootAsset, $args): UseInstructionCollection {
            $instructions = new UseInstructionCollection();
            $instructions->rootUrl = $rootAsset->createUrl($args);
            foreach ($rootAsset->getAssetChildren() as $asset) {
                if ($asset->isUseManifestInstruction()) {
                    $instructions->manifestUrls[] = $asset->createUrl($args);
                }
                if ($asset->isUseDocumentInstruction()) {
                    $instructions->documentUrls[] = $asset->createUrl($args);
                }
                if ($asset->isUseTemplateInstruction()) {
                    $instructions->templateUrl = $asset->createUrl($args);
                }
            }
            return $instructions;
        };
        $getLinkInstructions = function (?AssetInterface $currentAsset = null) use ($rootAsset, $args, &$getLinkInstructions): LinkInstructionCollection {
            if ($currentAsset === null) {
                $currentAsset = $rootAsset;
            }
            $instructions = new LinkInstructionCollection();
            foreach ($currentAsset->getAssetChildren() as $asset) {
                if ($asset->isUseDocumentInstruction()) {
                    $url = $asset->lookupExecutable($args)->createUrl();
                    $instructions->mergeWith($getLinkInstructions(Module::resolveToAsset($url)));
                }
                if ($asset->isLinkStylesheetInstruction()) {
                    $instructions->stylesheetUrls[] = $asset->lookupExecutable($args)->createUrl();
                }
                if ($asset->isLinkScriptInstruction()) {
                    $instructions->scriptUrls[] = $asset->lookupExecutable($args)->createUrl();
                }
                if ($asset->isLinkModuleInstruction()) {
                    $instructions->moduleUrls[] = $asset->lookupExecutable($args)->createUrl();
                }
            }
            return $instructions;
        };
        $resultBuilder = new TransformationResultBuilder($getUseInstructions, $getLinkInstructions);
        return new ExecutableStrategies($resultBuilder);
    }
}


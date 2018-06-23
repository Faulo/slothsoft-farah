<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\TransformationResultBuilder;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;

class FromManifestExecutableBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $rootAsset = $context;
        $getUseInstructions = function() use ($rootAsset) : UseInstructionCollection {
            $instructions = new UseInstructionCollection();
            $instructions->rootAsset = $rootAsset;
            foreach ($rootAsset->getAssetChildren() as $asset) {
                if ($asset->isUseManifestInstruction()) {
                    $instructions->manifestAssets[] = $asset;
                }
                if ($asset->isUseDocumentInstruction()) {
                    $instructions->documentAssets[] = $asset;
                }
                if ($asset->isUseTemplateInstruction()) {
                    $instructions->templateAsset = $asset;
                }
            }
            return $instructions;
        };
        $getLinkInstructions = function(?AssetInterface $currentAsset = null) use ($rootAsset, &$getLinkInstructions) : LinkInstructionCollection {
            if ($currentAsset === null) {
                $currentAsset = $rootAsset;
            }
            $instructions = new LinkInstructionCollection();
            if ($currentAsset->isUseDocumentInstruction()) {
                $refAsset = $currentAsset->getUseInstructionAsset();
                if ($refAsset !== $currentAsset) {
                    $instructions->mergeWith($getLinkInstructions($refAsset));
                }
            }
            foreach ($currentAsset->getAssetChildren() as $asset) {
                if ($asset->isUseDocumentInstruction()) {
                    $instructions->mergeWith($getLinkInstructions($asset));
                }
                if ($asset->isLinkStylesheetInstruction()) {
                    $instructions->stylesheetAssets[] = $asset->getLinkInstructionAsset();
                }
                if ($asset->isLinkScriptInstruction()) {
                    $instructions->scriptAssets[] = $asset->getLinkInstructionAsset();
                }
            }
            return $instructions;
        };
        $resultBuilder = new TransformationResultBuilder($getUseInstructions, $getLinkInstructions);
        return new ExecutableStrategies($resultBuilder);
    }
}


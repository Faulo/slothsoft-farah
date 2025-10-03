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
use Slothsoft\Farah\Module\Manifest\Manifest;

class FromManifestExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $rootAsset = $context;
        $getUseInstructions = function () use ($rootAsset, $args): UseInstructionCollection {
            $instructions = new UseInstructionCollection($rootAsset->createUrl($args));
            /** @var AssetInterface $asset */
            foreach ($rootAsset->getAssetChildren() as $asset) {
                switch ($args->get(Manifest::PARAM_LOAD, '')) {
                    case Manifest::PARAM_LOAD_TREE:
                        if ($asset->isUseManifestInstruction() or $asset->isUseDocumentInstruction() or $asset->isUseTemplateInstruction()) {
                            $instructions->documentUrls[] = $asset->createRealUrl($args);
                        }
                        break;
                    case Manifest::PARAM_LOAD_CHILDREN:
                        if ($asset->isUseManifestInstruction() or $asset->isUseDocumentInstruction() or $asset->isUseTemplateInstruction()) {
                            $instructions->documentUrls[] = $asset->createRealUrl($args->withoutArgument('load'));
                        }
                        break;
                    default:
                        if ($asset->isUseManifestInstruction()) {
                            $instructions->manifestUrls[] = $asset->createRealUrl($args);
                        }
                        if ($asset->isUseDocumentInstruction()) {
                            $instructions->documentUrls[] = $asset->createRealUrl($args);
                        }
                        if ($asset->isUseTemplateInstruction()) {
                            $instructions->templateUrl = $asset->createRealUrl($args);
                        }
                        break;
                }
            }
            return $instructions;
        };
        $getLinkInstructions = null;
        $getLinkInstructions = function (?AssetInterface $currentAsset = null) use ($rootAsset, $args, &$getLinkInstructions): LinkInstructionCollection {
            if ($currentAsset === null) {
                $currentAsset = $rootAsset;
            }
            $instructions = new LinkInstructionCollection();
            /** @var AssetInterface $asset */
            foreach ($currentAsset->getAssetChildren() as $asset) {
                if ($asset->isUseDocumentInstruction()) {
                    $url = $asset->lookupExecutable($args)->createRealUrl();
                    $instructions->mergeWith($getLinkInstructions(Module::resolveToAsset($url)));
                }
                if ($asset->isLinkStylesheetInstruction()) {
                    $instructions->stylesheetUrls[] = $asset->lookupExecutable($args)->createRealUrl();
                }
                if ($asset->isLinkScriptInstruction()) {
                    $instructions->scriptUrls[] = $asset->lookupExecutable($args)->createRealUrl();
                }
                if ($asset->isLinkModuleInstruction()) {
                    $instructions->moduleUrls[] = $asset->lookupExecutable($args)->createRealUrl();
                }
            }
            return $instructions;
        };
        $resultBuilder = new TransformationResultBuilder($getUseInstructions, $getLinkInstructions);
        return new ExecutableStrategies($resultBuilder);
    }
}


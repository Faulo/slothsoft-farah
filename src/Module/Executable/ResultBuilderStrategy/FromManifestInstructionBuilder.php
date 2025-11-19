<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;
use Slothsoft\Farah\Module\Manifest\Manifest;

class FromManifestInstructionBuilder implements InstructionBuilderStrategyInterface {
    
    public function buildUseInstructions(AssetInterface $rootAsset, FarahUrlArguments $args): UseInstructionCollection {
        $instructions = new UseInstructionCollection($rootAsset->createUrl($args));
        /** @var AssetInterface $asset */
        foreach ($rootAsset->getAssetChildren() as $asset) {
            $name = $asset->getManifestElement()->getAttribute(Manifest::ATTR_NAME);
            switch ($args->get(Manifest::PARAM_LOAD, '')) {
                case Manifest::PARAM_LOAD_TREE:
                    if ($asset->isUseManifestInstruction() or $asset->isUseDocumentInstruction() or $asset->isUseTemplateInstruction()) {
                        $instructions->addDocumentData($asset->createRealUrl($args), $name);
                    }
                    break;
                case Manifest::PARAM_LOAD_CHILDREN:
                    if ($asset->isUseManifestInstruction() or $asset->isUseDocumentInstruction() or $asset->isUseTemplateInstruction()) {
                        $instructions->addDocumentData($asset->createRealUrl($args->withoutArgument(Manifest::PARAM_LOAD)), $name);
                    }
                    break;
                default:
                    if ($asset->isUseManifestInstruction()) {
                        $instructions->addManifestData($asset->createRealUrl($args), $name);
                    }
                    if ($asset->isUseDocumentInstruction()) {
                        $instructions->addDocumentData($asset->createRealUrl($args), $name);
                    }
                    if ($asset->isUseTemplateInstruction()) {
                        $instructions->templateUrl = $asset->createRealUrl($args);
                    }
                    break;
            }
        }
        return $instructions;
    }
    
    public function buildLinkInstructions(AssetInterface $rootAsset, FarahUrlArguments $args): LinkInstructionCollection {
        $instructions = new LinkInstructionCollection();
        /** @var AssetInterface $asset */
        foreach ($rootAsset->getAssetChildren() as $asset) {
            if ($asset->isUseDocumentInstruction()) {
                $executable = $asset->lookupExecutable($args);
                $instructions->mergeWith($executable->lookupLinkInstructions(), true);
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
            if ($asset->isLinkContentInstruction()) {
                $instructions->contentUrls[] = $asset->lookupExecutable($args)->createRealUrl();
            }
            if ($asset->isLinkDictionaryInstruction()) {
                $instructions->dictionaryUrls[] = $asset->lookupExecutable($args)->createRealUrl();
            }
        }
        return $instructions;
    }
}


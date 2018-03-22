<?php
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;

class InstructionCollector
{
    public static function createFromAsset(AssetInterface $asset) {
        return self::createFromInstructions($asset->getChildren());
    }
    public static function createFromInstructions(array $nodeList) {
        $collector = new self();
        foreach ($nodeList as $node) {
            if ($node->isUseDocument()) {
                assert($node instanceof UseDocumentInstructionInterface, "To <use-document> {get_class($node)}, it must implement UseDocumentInstructionInterface.");
                $collector->documentInstructions[] = $node;
                
                $asset = $node->getReferencedDocumentAsset();
                $collector->stylesheetAssets += $asset->lookupLinkedStylesheets();
                $collector->scriptAssets += $asset->lookupLinkedScripts();
            }
            if ($node->isUseManifest()) {
                assert($node instanceof UseManifestInstructionInterface, "To <use-manifest> {get_class($node)}, it must implement UseManifestInstructionInterface.");
                $collector->manifestInstructions[] = $node;
            }
            if ($node->isUseTemplate()) {
                assert($node instanceof UseTemplateInstructionInterface, "To <use-template> {get_class($node)}, it must implement UseTemplateInstructionInterface.");
                $collector->templateInstruction = $node;
            }
            if ($node->isUseStylesheet()) {
                assert($node instanceof UseStylesheetInstructionInterface, "To <use-stylesheet> {get_class($node)}, it must implement UseStylesheetInstructionInterface.");
                $asset = $node->getReferencedStylesheetAsset();
                $collector->stylesheetAssets[(string) $asset] = $asset;
            }
            if ($node->isUseScript()) {
                assert($node instanceof UseScriptInstructionInterface, "To <use-script> {get_class($node)}, it must implement UseScriptInstructionInterface.");
                $asset = $node->getReferencedScriptAsset();
                $collector->scriptAssets[(string) $asset] = $asset;
            }
        }
        return $collector;
    }
    
    public $documentInstructions = [];
    public $manifestInstructions = [];
    
    public $templateInstruction = null;
    
    public $stylesheetAssets = [];
    public $scriptAssets = [];
}


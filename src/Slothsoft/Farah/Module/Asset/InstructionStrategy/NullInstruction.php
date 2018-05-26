<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class NullInstruction implements InstructionStrategyInterface
{
    public function isImportSelf(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isImportChildren(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isUseManifest(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isUseDocument(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isUseTemplate(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isLinkStylesheet(AssetInterface $context): bool
    {
        return false;
    }
    
    public function isLinkScript(AssetInterface $context): bool
    {
        return false;
    }
    public function getReferencedAsset(AssetInterface $context): AssetInterface
    {
        return null;
    }
}


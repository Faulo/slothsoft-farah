<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

class FromManifestInstruction implements InstructionStrategyInterface {
    
    public function isImportSelf(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute(Manifest::ATTR_IMPORT, '') === Manifest::ATTR_IMPORT_SELF;
    }
    
    public function isImportChildren(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute(Manifest::ATTR_IMPORT, '') === Manifest::ATTR_IMPORT_CHILDREN;
    }
    
    public function isUseManifest(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute(Manifest::ATTR_USE, '') === Manifest::ATTR_USE_MANIFEST;
    }
    
    public function isUseDocument(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute(Manifest::ATTR_USE, '') === Manifest::ATTR_USE_DOCUMENT;
    }
    
    public function isUseTemplate(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute(Manifest::ATTR_USE, '') === Manifest::ATTR_USE_TEMPLATE;
    }
    
    public function isLinkStylesheet(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === Manifest::ATTR_USE_STYLESHEET;
    }
    
    public function isLinkScript(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === Manifest::ATTR_USE_SCRIPT;
    }
    
    public function isLinkModule(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === Manifest::ATTR_USE_MODULE;
    }
    
    public function isParameterSupplier(AssetInterface $context): bool {
        return false;
    }
}


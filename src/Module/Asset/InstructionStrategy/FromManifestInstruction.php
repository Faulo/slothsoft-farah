<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class FromManifestInstruction implements InstructionStrategyInterface {

    public function isImportSelf(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('import', '') === 'self';
    }

    public function isImportChildren(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('import', '') === 'children';
    }

    public function isUseManifest(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('use', '') === 'manifest';
    }

    public function isUseDocument(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('use', '') === 'document';
    }

    public function isUseTemplate(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('use', '') === 'template';
    }

    public function isLinkStylesheet(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === 'stylesheet';
    }

    public function isLinkScript(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === 'script';
    }

    public function isLinkModule(AssetInterface $context): bool {
        return $context->getManifestElement()->getAttribute('link', '') === 'module';
    }

    public function isParameterSupplier(AssetInterface $context): bool {
        return false;
    }
}


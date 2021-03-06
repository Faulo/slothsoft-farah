<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

interface InstructionStrategyInterface {

    public function isImportSelf(AssetInterface $context): bool;

    public function isImportChildren(AssetInterface $context): bool;

    public function isUseManifest(AssetInterface $context): bool;

    public function isUseTemplate(AssetInterface $context): bool;

    public function isUseDocument(AssetInterface $context): bool;

    public function isLinkStylesheet(AssetInterface $context): bool;

    public function isLinkScript(AssetInterface $context): bool;

    public function isLinkModule(AssetInterface $context): bool;

    public function isParameterSupplier(AssetInterface $context): bool;
}


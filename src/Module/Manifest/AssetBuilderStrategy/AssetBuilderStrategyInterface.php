<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetStrategies;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;

interface AssetBuilderStrategyInterface
{

    public function normalizeElement(LeanElement $element, ?LeanElement $parent = null): void;

    public function buildAssetStrategies(ManifestInterface $ownerManifest, LeanElement $element): AssetStrategies;
}


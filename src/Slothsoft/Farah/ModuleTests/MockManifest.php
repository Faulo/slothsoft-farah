<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;

class MockManifest implements ManifestInterface
{

    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void
    {}

    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl
    {}

    public function lookupAsset($path): AssetInterface
    {}

    public function createAsset(LeanElement $element): AssetInterface
    {}

    public function getId(): string
    {}

    public function normalizeManifestTree(LeanElement $root): void
    {}
}


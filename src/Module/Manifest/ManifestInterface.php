<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use SplFileInfo;

interface ManifestInterface {
    
    /**
     * The Farah URL that represents this module.
     * $this === Module::resolveToManifest($this->getId())
     */
    public function getId(): string;
    
    /**
     * Build a Farah URL for the given path, with this module's URL as base.
     */
    public function createUrl(FarahUrlPath|string|null $path = null, FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): FarahUrl;
    
    public function lookupAsset(FarahUrlPath|string $path): AssetInterface;
    
    public function clearCachedAssets(): void;
    
    /**
     * Returns a handle for a file inside the asset directory of this manifest.
     */
    public function createManifestFile(string $fileName): SplFileInfo;
    
    public function createCacheFile(string $fileName, FarahUrlPath|string|null $path = null, FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): SplFileInfo;
    
    public function createDataFile(string $fileName, FarahUrlPath|string|null $path = null, FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): SplFileInfo;
    
    /**
     * Set any missing attributes according to the AssetBuilderStrategy.
     */
    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void;
    
    /**
     * Set any missing attributes in the whole tree according to the AssetBuilderStrategy.
     */
    public function normalizeManifestTree(LeanElement $root): void;
}


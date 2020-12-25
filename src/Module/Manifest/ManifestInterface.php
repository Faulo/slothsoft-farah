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
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Build a Farah URL for the given path, with this module's URL as base.
     *
     * @param FarahUrlPath|string $path
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl;

    /**
     *
     * @param FarahUrlPath|string $path
     * @return AssetInterface
     */
    public function lookupAsset($path): AssetInterface;

    /**
     *
     * @param LeanElement $element
     * @return AssetInterface
     */
    public function createAsset(LeanElement $element): AssetInterface;

    /**
     * Returns a handle for a file inside the asset directory of this manifest.
     *
     * @param string $fileName
     * @return SplFileInfo
     */
    public function createManifestFile(string $fileName): SplFileInfo;

    /**
     *
     * @param string $fileName
     * @param FarahUrlPath|string $path
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return SplFileInfo
     */
    public function createCacheFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo;

    /**
     *
     * @param string $fileName
     * @param FarahUrlPath|string $path
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return SplFileInfo
     */
    public function createDataFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo;

    /**
     * Set any missing attributes according to the AssetBuilderStrategy.
     *
     * @param LeanElement $parent
     * @param LeanElement $child
     */
    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void;

    /*
     * Set any missing attributes in the whole tree according to the AssetBuilderStrategy.
     *
     * @param LeanElement $parent
     * @param LeanElement $child
     */
    public function normalizeManifestTree(LeanElement $root): void;
}


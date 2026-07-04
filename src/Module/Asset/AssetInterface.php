<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

/**
 *
 * @author Daniel Schulz
 *
 */
interface AssetInterface {
    
    public function __toString(): string;
    
    public function getManifest(): ManifestInterface;
    
    public function getManifestElement(): LeanElement;
    
    /**
     * Used by Manifest to create the asset tree.
     */
    public function getChildManifestElement(string $name): LeanElement;
    
    /**
     * Get all child assets.
     */
    public function getAssetChildren(): iterable;
    
    /**
     * Traverse to a descendant asset.
     */
    public function traverseTo(string $path): AssetInterface;
    
    public function createCacheFile(string $fileName, FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): SplFileInfo;
    
    public function createDataFile(string $fileName, FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): SplFileInfo;
    
    /**
     * Create a FarahUrl for this asset, with arguments and stream set as supplied.
     */
    public function createUrl(FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): FarahUrl;
    
    /**
     * Create a FarahUrl for this asset, with arguments and stream set as supplied.
     * Follows any ref attributes, if present.
     */
    public function createRealUrl(FarahUrlArguments|string|null $args = null, FarahUrlStreamIdentifier|string|null $fragment = null): FarahUrl;
    
    /**
     * Get the FarahUrlPath for this asset.
     */
    public function getUrlPath(): FarahUrlPath;
    
    /**
     * Get the filesystem entry for this asset.
     * This might be a file, a directory, or it might not physically exist.
     */
    public function getFileInfo(): SplFileInfo;
    
    /**
     * Create the executable for this asset, with the arguments supplied.
     */
    public function lookupExecutable(?FarahUrlArguments $args = null): ExecutableInterface;
    
    public function isImportSelfInstruction(): bool;
    
    public function isImportChildrenInstruction(): bool;
    
    public function isUseManifestInstruction(): bool;
    
    public function isUseTemplateInstruction(): bool;
    
    public function isUseDocumentInstruction(): bool;
    
    public function isLinkStylesheetInstruction(): bool;
    
    public function isLinkScriptInstruction(): bool;
    
    public function isLinkModuleInstruction(): bool;
    
    public function isLinkContentInstruction(): bool;
    
    public function isLinkDictionaryInstruction(): bool;
    
    public function isParameterSupplierInstruction(): bool;
    
    /**
     * Set any missing attributes according to the manifest's AssetBuilderStrategy.
     */
    public function normalizeManifestElement(LeanElement $child): void;
    
    /**
     * Get the parameters supplied by this asset's ParameterSupplierStrategy.
     */
    public function getSuppliedParameters(): iterable;
}


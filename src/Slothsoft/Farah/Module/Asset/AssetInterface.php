<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use SplFileInfo;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface
{

    public function __toString(): string;

    /**
     *
     * @return LeanElement
     */
    public function getManifestElement(): LeanElement;

    /**
     * Get all child assets.
     *
     * @return AssetInterface[]
     */
    public function getAssetChildren(): iterable;

    /**
     * Traverse to a descendant asset.
     *
     * @param string $path
     * @return AssetInterface
     */
    public function traverseTo(string $path): AssetInterface;

    /**
     * Create a FarahUrl for this asset, with arguments and stream set as supplied.
     *
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public function createUrl($args = null, $fragment = null): FarahUrl;

    /**
     * Get the FarahUrlPath for this asset.
     *
     * @return FarahUrlPath|NULL
     */
    public function getUrlPath(): FarahUrlPath;

    /**
     * Get the filesystem entry for this asset.
     * This might be a file, a directory, or it might not physically exist.
     *
     * @return FarahUrlPath|NULL
     */
    public function getFileInfo(): SplFileInfo;

    /**
     * Create the executable for this asset, with the arguments supplied.
     *
     * @param FarahUrlArguments $args
     * @return ExecutableInterface
     */
    public function lookupExecutable(FarahUrlArguments $args = null): ExecutableInterface;

    public function isImportSelfInstruction(): bool;

    public function isImportChildrenInstruction(): bool;

    public function isUseManifestInstruction(): bool;

    public function isUseTemplateInstruction(): bool;

    public function isUseDocumentInstruction(): bool;

    public function isLinkStylesheetInstruction(): bool;

    public function isLinkScriptInstruction(): bool;

    public function isLinkModuleInstruction(): bool;

    public function isParameterSupplierInstruction(): bool;

    /**
     * Set any missing attributes according to the manifest's AssetBuilderStrategy.
     *
     * @param LeanElement $child
     */
    public function normalizeManifestElement(LeanElement $child): void;

    /**
     * Get the parameters supplied by this assets's ParameterSupplierStrategy.
     *
     * @return iterable
     */
    public function getSuppliedParameters(): iterable;
}


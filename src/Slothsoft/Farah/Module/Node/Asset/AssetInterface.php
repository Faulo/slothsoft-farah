<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface extends ModuleNodeInterface
{

    public function __toString(): string;

    /**
     * The Farah URL that represents this asset.
     * "url" attribute
     *
     * @return string
     */
    public function getId(): string;

    /**
     * "name" attribute
     *
     * @return string
     */
    public function getName(): string;

    /**
     * "assetpath" attribute
     *
     * @return string
     */
    public function getAssetPath(): string;

    /**
     * "use" attribute
     *
     * @return string
     */
    public function getUse(): string;

    /**
     * Get all child ModuleNodes that implement AssetInterface.
     *
     * @return array
     */
    public function getAssetChildren(): array;

    public function traverseTo(string $path): AssetInterface;

    /**
     * Remove or add parameters from $args as defined by this asset's parameter filter.
     *
     * @param FarahUrlArguments $args
     * @return FarahUrlArguments
     */
    public function applyParameterFilter(FarahUrlArguments $args): FarahUrlArguments;

    /**
     * Create a FarahUrl for this asset, with arguments and stream set as supplied.
     *
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public function createUrl($args = null, $fragment = null): FarahUrl;

    /**
     * Create the executable for this asset, with the arguments supplied.
     *
     * @param FarahUrlArguments $args
     * @return ExecutableInterface
     */
    public function lookupExecutable(FarahUrlArguments $args = null): ExecutableInterface;

    /**
     * Find all manifest, document, and template instructions among the immediate children,
     * and find all stylesheet and script instructions among all descendants and referenced assets.
     *
     * @return InstructionCollector
     */
    public function collectInstructions(): InstructionCollector;
}


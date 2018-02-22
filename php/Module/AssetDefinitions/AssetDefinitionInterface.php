<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetDefinitionInterface
{

    public function init(Module $ownerModule, LeanElement $element, array $children);

    public function getOwnerModule(): Module;

    public function getElement(): LeanElement;

    public function getElementTag(): string;

    public function getElementAttribute(string $key): string;

    public function hasElementAttribute(string $key): bool;

    public function getChildren(): array;

    public function createChildDefinition(LeanElement $element): AssetDefinitionInterface;

    public function getId(): string;

    public function getName(): string;

    public function getPath(): string;

    public function getRealPath(): string;

    public function getAssetPath(): string;

    public function traverseTo(string $path): AssetDefinitionInterface;

    public function getPathResolver(): PathResolverInterface;

    public function filterParameters(array &$args): bool;

    public function getParameterFilter(): ParameterFilterInterface;

    public function toUrl(array $args): FarahUrl;
}


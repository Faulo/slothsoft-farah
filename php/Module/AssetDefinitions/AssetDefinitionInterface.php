<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

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

    public function init(Module $ownerModule, string $tag, array $attributes);

    public function getOwnerModule(): Module;

    public function getTag(): string;

    public function getId(): string;

    public function getName(): string;

    public function getRealPath(): string;

    public function getAssetPath(): string;

    public function getAttribute(string $key): string;

    public function hasAttribute(string $key): bool;
    
    public function setAttribute(string $key, string $val);

    public function getAttributes(): array;

    public function appendChild(AssetDefinitionInterface $asset);

    public function getChildren(): array;

    public function traverseTo(string $path): AssetDefinitionInterface;

    public function getPathResolver(): PathResolverInterface;

    public function filterParameters(array &$args): bool;

    public function getParameterFilter(): ParameterFilterInterface;

    public function toUrl(array $args): FarahUrl;
}


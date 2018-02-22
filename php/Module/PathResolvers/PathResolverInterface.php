<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface PathResolverInterface
{

    public function resolvePath(string $path): AssetDefinitionInterface;

    public function getPathMap(): array;
}


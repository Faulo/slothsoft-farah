<?php
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface PathResolverInterface
{
    public function resolvePath(string $path) : AssetDefinition;
    public function getPathMap() : array;
}


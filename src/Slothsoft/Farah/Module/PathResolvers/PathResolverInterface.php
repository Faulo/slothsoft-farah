<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface PathResolverInterface
{

    public function resolvePath(string $path): AssetInterface;

    public function getPathMap(): array;
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Path resolver strategy for null manifest asset paths.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class NullPathResolver implements PathResolverStrategyInterface {
    use ResolvePathFromManifestTrait;
    
    public function loadChildren(AssetInterface $context): iterable {
        return [];
    }
}


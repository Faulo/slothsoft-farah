<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class NullPathResolver implements PathResolverStrategyInterface {
    use ResolvePathFromManifestTrait;

    public function loadChildren(AssetInterface $context): iterable {
        return [];
    }
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

final class FromManifestPathResolver implements PathResolverStrategyInterface {
    use LoadChildrenFromManifestTrait;
    use ResolvePathFromManifestTrait;
}


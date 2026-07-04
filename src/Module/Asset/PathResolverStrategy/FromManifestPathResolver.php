<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

/**
 * Path resolver strategy that reads child paths from manifest elements.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class FromManifestPathResolver implements PathResolverStrategyInterface {
    use LoadChildrenFromManifestTrait;
    use ResolvePathFromManifestTrait;
}


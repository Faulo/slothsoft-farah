<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Path resolver strategy for catch all manifest asset paths.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class CatchAllPathResolver implements PathResolverStrategyInterface {
    use LoadChildrenFromManifestTrait;
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        return $context->getManifestElement();
    }
}


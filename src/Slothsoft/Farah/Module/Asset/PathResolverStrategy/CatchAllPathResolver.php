<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class CatchAllPathResolver implements PathResolverStrategyInterface
{
    use LoadChildrenFromManifestTrait;
    
    public function resolvePath(AssetInterface $context, string $name) : LeanElement {
        return $context->getManifestElement();
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DirectoryAsset extends PhysicalAssetImplementation
{

    protected function loadPathResolver(): PathResolverInterface
    {
        $map = [];
        $map['/'] = $this;
        foreach ($this->getAssetChildren() as $asset) {
            $name = $asset->getName();
            if ($name !== '/') {
                $map["/$name"] = $asset;
            }
        }
        return PathResolverCatalog::createMapPathResolver($this, $map);
    }
}


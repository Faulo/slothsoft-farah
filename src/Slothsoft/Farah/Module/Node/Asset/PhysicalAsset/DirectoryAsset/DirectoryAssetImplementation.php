<?php
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\DirectoryAsset;

use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetImplementation;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

class DirectoryAssetImplementation extends PhysicalAssetImplementation implements DirectoryAssetInterface
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


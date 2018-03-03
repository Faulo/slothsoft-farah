<?php
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class PathResolverCatalog
{

    public static function createNullPathResolver(AssetInterface $asset)
    {
        return new NullPathResolver($asset);
    }

    public static function createCatchAllPathResolver(AssetInterface $asset)
    {
        return new CatchAllPathResolver($asset);
    }

    public static function createMapPathResolver(AssetInterface $asset, array $assetMap)
    {
        return new MapPathResolver($asset, $assetMap);
    }

    public static function createResourceDirectoryPathResolver(AssetInterface $asset)
    {
        return new ResourceDirectoryPathResolver($asset);
    }
}


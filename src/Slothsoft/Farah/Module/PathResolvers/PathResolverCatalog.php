<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class PathResolverCatalog
{

    public static function createNullPathResolver(AssetInterface $asset) : NullPathResolver
    {
        return new NullPathResolver($asset);
    }

    public static function createCatchAllPathResolver(AssetInterface $asset) : CatchAllPathResolver
    {
        return new CatchAllPathResolver($asset);
    }

    public static function createMapPathResolver(AssetInterface $asset, array $assetMap) : MapPathResolver
    {
        return new MapPathResolver($asset, $assetMap);
    }

    public static function createResourceDirectoryPathResolver(AssetInterface $asset) : ResourceDirectoryPathResolver
    {
        return new ResourceDirectoryPathResolver($asset);
    }
}


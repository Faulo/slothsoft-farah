<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{

    private $asset;

    private $pathMap = [];

    public function __construct(PhysicalAssetInterface $asset)
    {
        $this->asset = $asset;
        $this->pathMap['/'] = $this->asset;
    }

    public function resolvePath(string $path): AssetInterface
    {
        if (! isset($this->pathMap[$path])) {
            $this->pathMap[$path] = $this->createChildResource($path);
        }
        return $this->pathMap[$path];
    }

    private function createChildResource(string $path): PhysicalAssetInterface
    {
        assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
        
        $childPath = $match[0];
        $childName = $match[1];
        $descendantPath = substr($path, strlen($childPath));
        
        if ($descendantPath === '') {
            return $this->asset->createChildResourceAsset($childName, $path);
        } else {
            return $this->resolvePath($childPath, true)->traverseTo($descendantPath);
        }
    }

    public function getPathMap(): array
    {
        ksort($this->pathMap);
        return $this->pathMap;
    }
}


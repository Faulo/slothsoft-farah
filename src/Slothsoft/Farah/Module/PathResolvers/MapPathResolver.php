<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MapPathResolver implements PathResolverInterface
{

    private $asset;

    private $assetMap;

    public function __construct(AssetInterface $asset, array $assetMap)
    {
        $this->asset = $asset;
        $this->assetMap = $assetMap;
    }

    public function resolvePath(string $path): AssetInterface
    {
        if (isset($this->assetMap[$path])) {
            return $this->assetMap[$path];
        } else {
            assert(preg_match('~^/[^/]+~', $path, $match), "Invalid asset path: $path");
            
            $childPath = $match[0];
            $descendantPath = substr($path, strlen($childPath));
            
            if (isset($this->assetMap[$childPath])) {
                return $descendantPath === '' ? $this->assetMap[$childPath] : $this->assetMap[$childPath]->traverseTo($descendantPath);
            }
        }
        throw ExceptionContext::append(new OutOfRangeException("Asset {$this->asset->getId()} did not provide a mapping for $path!"), [
            'asset' => $this->asset,
            'class' => __CLASS__,
        ]);
    }

    public function getPathMap(): array
    {
        return $this->assetMap;
    }
}


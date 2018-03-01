<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Assets\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class CatchAllPathResolver implements PathResolverInterface
{

    private $asset;

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    public function resolvePath(string $path): AssetInterface
    {
        if ($path === '/') {
            return $this->asset;
        } else {
            $element = $this->asset->getElement()->withAttributes([
                'path' => $path,
                'assetpath' => $this->asset->getAssetPath() . $path,
                'realpath' => $this->asset->getRealPath() . $path,
            ]);
            return $this->asset->addChildElement($element);
        }
    }

    public function getPathMap(): array
    {
        return [
            '/' => $this->asset
        ];
    }
}


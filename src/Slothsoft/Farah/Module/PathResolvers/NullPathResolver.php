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
class NullPathResolver implements PathResolverInterface
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
        }
        throw ExceptionContext::append(new OutOfRangeException("Cannot traverse from {$this->asset->getId()} to $path."), [
            'asset' => $this->asset,
            'class' => __CLASS__,
        ]);
    }

    public function getPathMap(): array
    {
        return [
            '/' => $this->asset
        ];
    }
}


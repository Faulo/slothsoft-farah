<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

class AssetPathNotFoundException extends \RuntimeException
{

    public function __construct(AssetInterface $asset, string $missingPath)
    {
        parent::__construct("Asset '{$asset->getId()}' does not contain an asset '$missingPath'!");
    }
}


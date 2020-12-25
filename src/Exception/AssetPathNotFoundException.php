<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use RuntimeException;

class AssetPathNotFoundException extends RuntimeException {

    public function __construct(AssetInterface $asset, string $missingPath) {
        parent::__construct("Asset '{$asset->getUrlPath()}' does not contain an asset '$missingPath'!");
    }
}


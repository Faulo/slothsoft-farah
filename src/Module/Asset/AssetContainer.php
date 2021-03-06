<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetContainer {

    private $map;

    public function __construct() {
        $this->map = new Map();
    }

    public function put(FarahUrlPath $id, AssetInterface $item) {
        $this->map->put($id, $item);
    }

    public function get(FarahUrlPath $id): AssetInterface {
        return $this->map->get($id);
    }

    public function has(FarahUrlPath $id): bool {
        return $this->map->hasKey($id);
    }
}


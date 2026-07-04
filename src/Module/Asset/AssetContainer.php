<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;

/**
 * Container for assets keyed by normalized Farah asset paths.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class AssetContainer {
    
    private Map $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlPath $id, AssetInterface $item): void {
        $this->map->put($id, $item);
    }
    
    public function get(FarahUrlPath $id): AssetInterface {
        return $this->map->get($id);
    }
    
    public function has(FarahUrlPath $id): bool {
        return $this->map->hasKey($id);
    }
    
    public function clear(): void {
        $this->map->clear();
    }
}


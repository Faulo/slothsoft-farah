<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

class ManifestContainer {
    
    private Map $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlAuthority $id, ManifestInterface $item) {
        $this->map->put($id, $item);
    }
    
    public function get(FarahUrlAuthority $id): ManifestInterface {
        return $this->map->get($id);
    }
    
    public function has(FarahUrlAuthority $id): bool {
        return $this->map->hasKey($id);
    }
    
    public function clear(): void {
        $this->map->clear();
    }
}


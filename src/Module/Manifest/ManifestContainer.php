<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Manifest;

use Ds\Map;
use IteratorAggregate;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Traversable;

/**
 * Container for manifests keyed by Farah URL authority.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ManifestContainer implements IteratorAggregate {
    
    private Map $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlAuthority $id, ManifestInterface $item): void {
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
    
    public function getIterator(): Traversable {
        return $this->map->getIterator();
    }
}


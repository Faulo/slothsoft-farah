<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;

/**
 * Container for results keyed by Farah URL stream identifiers.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ResultContainer {
    
    private Map $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlStreamIdentifier $id, ResultInterface $item): void {
        $this->map->put($id, $item);
    }
    
    public function get(FarahUrlStreamIdentifier $id): ResultInterface {
        return $this->map->get($id);
    }
    
    public function has(FarahUrlStreamIdentifier $id): bool {
        return $this->map->hasKey($id);
    }
    
    public function clear(): void {
        $this->map->clear();
    }
}


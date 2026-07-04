<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

/**
 * Container for executables keyed by Farah URL arguments.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ExecutableContainer {
    
    private Map $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlArguments $id, ExecutableInterface $item): void {
        $this->map->put($id, $item);
    }
    
    public function get(FarahUrlArguments $id): ExecutableInterface {
        return $this->map->get($id);
    }
    
    public function has(FarahUrlArguments $id): bool {
        return $this->map->hasKey($id);
    }
    
    public function clear(): void {
        $this->map->clear();
    }
}


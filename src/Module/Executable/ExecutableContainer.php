<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExecutableContainer {
    
    private $map;
    
    public function __construct() {
        $this->map = new Map();
    }
    
    public function put(FarahUrlArguments $id, ExecutableInterface $item) {
        $this->map->put($id, $item);
    }
    
    public function get(FarahUrlArguments $id): ExecutableInterface {
        return $this->map->get($id);
    }
    
    public function has(FarahUrlArguments $id): bool {
        return $this->map->hasKey($id);
    }
}


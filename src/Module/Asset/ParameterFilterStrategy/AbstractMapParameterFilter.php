<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 * Parameter filter strategy for abstract map asset parameters.
 *
 * @author Daniel Schulz
 * @since 2018-03-05
 */
abstract class AbstractMapParameterFilter implements ParameterFilterStrategyInterface {
    
    abstract protected function createValueSanitizers(): array;
    
    private array $map;
    
    public function __construct() {
        $this->map = $this->createValueSanitizers();
    }
    
    public function isAllowedName(string $name): bool {
        return isset($this->map[$name]);
    }
    
    public function getValueSanitizers(): iterable {
        return $this->map;
    }
}


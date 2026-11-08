<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Core\IO\Sanitizer\SanitizerInterface;

/**
 * Parameter filter strategy for abstract map asset parameters.
 *
 * @author Daniel Schulz
 * @since 2018-03-05
 */
abstract class AbstractMapParameterFilter implements ParameterFilterStrategyInterface {
    
    /**
     * @return array<string, SanitizerInterface>
     */
    abstract protected function createValueSanitizers(): array;
    
    /** @var array<string, SanitizerInterface> */
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


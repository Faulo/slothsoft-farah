<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AllowAllParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return true;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


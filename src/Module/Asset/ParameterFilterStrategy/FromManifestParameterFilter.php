<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FromManifestParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return $name === 'load';
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


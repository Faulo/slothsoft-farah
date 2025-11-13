<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FromFilesystemParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return $name === Manifest::PARAM_INCLUDES;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DenyAllParameterFilter implements ParameterFilterStrategyInterface {

    public function isAllowedName(string $name): bool {
        return false;
    }

    public function getValueSanitizers(): iterable {
        return [];
    }
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 * Parameter filter strategy for deny all asset parameters.
 *
 * @author Daniel Schulz
 * @since 2018-02-21
 */
final class DenyAllParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return false;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


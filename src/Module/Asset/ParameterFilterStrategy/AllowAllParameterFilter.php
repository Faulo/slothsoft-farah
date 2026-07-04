<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

/**
 * Parameter filter strategy for allow all asset parameters.
 *
 * @author Daniel Schulz
 * @since 2018-02-21
 */
final class AllowAllParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return true;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


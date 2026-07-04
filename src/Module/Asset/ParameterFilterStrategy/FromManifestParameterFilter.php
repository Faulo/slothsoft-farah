<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * Parameter filter strategy that reads allowed parameters from manifest metadata.
 *
 * @author Daniel Schulz
 * @since 2025-07-05
 */
final class FromManifestParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return $name === Manifest::PARAM_LOAD;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


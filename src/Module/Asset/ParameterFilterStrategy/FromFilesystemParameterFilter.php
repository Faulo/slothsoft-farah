<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * Parameter filter strategy that restricts parameters for file-backed assets.
 *
 * @author Daniel Schulz
 * @since 2018-02-21
 */
final class FromFilesystemParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return $name === Manifest::PARAM_INCLUDES;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}


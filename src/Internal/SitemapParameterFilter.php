<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * Allows the execution parameters used by the sitemap generator.
 *
 * @author Daniel Schulz
 * @since 2026-09-04
 */
final class SitemapParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return $name === Manifest::PARAM_LOAD;
    }
    
    public function getValueSanitizers(): iterable {
        return [];
    }
}

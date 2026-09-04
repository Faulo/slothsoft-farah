<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;

/**
 * Preserves sitemap parameters while normalizing the generated web URL scheme.
 *
 * @author Daniel Schulz
 * @since 2026-09-04
 */
final class CurrentSitemapParameterFilter implements ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool {
        return true;
    }
    
    public function getValueSanitizers(): iterable {
        return [
            SitemapBuilder::PARAM_SCHEME => new CurrentRequestSchemeSanitizer()
        ];
    }
}

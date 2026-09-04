<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\IO\Sanitizer\SanitizerInterface;
use Slothsoft\Farah\Http\WebScheme;
use Slothsoft\Farah\Kernel;

/**
 * Replaces a sitemap scheme parameter with the current request scheme.
 *
 * @author Daniel Schulz
 * @since 2026-09-04
 */
final class CurrentRequestSchemeSanitizer implements SanitizerInterface {
    
    public function apply($value): string {
        try {
            $scheme = Kernel::getCurrentRequest()->getUri()->getScheme();
            if (WebScheme::isSupported($scheme)) {
                return WebScheme::normalize($scheme);
            }
        } catch (ConfigurationRequiredException) {
        }
        $scheme = (string) $value;
        return WebScheme::isSupported($scheme) ? WebScheme::normalize($scheme) : WebScheme::HTTP;
    }
    
    public function getDefault(): string {
        return $this->apply(WebScheme::HTTP);
    }
}

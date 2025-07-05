<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Core\IO\Sanitizer\StringSanitizer;

/**
 *
 * @author Daniel Schulz
 *
 */
class FromManifestParameterFilter extends AbstractMapParameterFilter {

    protected function createValueSanitizers(): array {
        return [
            'load' => new StringSanitizer()
        ];
    }
}


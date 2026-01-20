<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\IO\Sanitizer\StringSanitizer;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\AbstractMapParameterFilter;

final class FontFaceParameterFilter extends AbstractMapParameterFilter {
    
    public const PARAM_ASSETS = 'assets';
    
    protected function createValueSanitizers(): array {
        return [
            self::PARAM_ASSETS => new StringSanitizer('')
        ];
    }
}


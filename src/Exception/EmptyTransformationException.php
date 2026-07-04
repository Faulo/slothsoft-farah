<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
final class EmptyTransformationException extends RuntimeException {
    
    public function __construct(string $source, ?string $template = '') {
        parent::__construct("Template '$template' transforming '$source' resulted in an empty document!");
    }
}

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;

/**
 * Exception type for the Farah empty transformation error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class EmptyTransformationException extends RuntimeException {
    
    public function __construct(string $source, ?string $template = '') {
        parent::__construct("Template '$template' transforming '$source' resulted in an empty document!");
    }
}

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for the Farah result type not supported error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class ResultTypeNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $type) {
        parent::__construct("Result type '$type' is not supported by this implementation.");
    }
}


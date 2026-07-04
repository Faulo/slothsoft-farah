<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for the Farah stream type not supported error condition.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class StreamTypeNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $type) {
        parent::__construct("Stream type '#$type' is not supported by this implementation.");
    }
}


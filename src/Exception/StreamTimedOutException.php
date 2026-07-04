<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use UnexpectedValueException;

/**
 * Exception type for the Farah stream timed out error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class StreamTimedOutException extends UnexpectedValueException {
    
    public function __construct(string $className) {
        parent::__construct("Stream '$className' timed out!");
    }
}


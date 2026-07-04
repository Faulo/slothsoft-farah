<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for the Farah protocol not supported error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class ProtocolNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $protocol) {
        parent::__construct("URL protocol '$protocol' is not supported by this implementation.");
    }
}


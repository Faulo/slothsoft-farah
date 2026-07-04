<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for the Farah namespace not supported error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class NamespaceNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $namespace) {
        parent::__construct("XML namespace '$namespace' is not supported by this implementation.");
    }
}


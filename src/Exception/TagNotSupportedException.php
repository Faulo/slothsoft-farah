<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use BadMethodCallException;

/**
 * Exception type for the Farah tag not supported error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class TagNotSupportedException extends BadMethodCallException {
    
    public function __construct(string $namespace, string $tag) {
        parent::__construct("<$tag xmlns=\"$namespace\"> is not supported by this implementation.");
    }
}


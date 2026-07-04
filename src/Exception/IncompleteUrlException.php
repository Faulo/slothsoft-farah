<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for Farah URLs that are missing required components.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class IncompleteUrlException extends InvalidArgumentException {
    
    public function __construct(string $url, string $missing) {
        parent::__construct("The URL '$url' is missing the $missing part.");
    }
}

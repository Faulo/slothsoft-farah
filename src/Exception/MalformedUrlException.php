<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for Farah URL strings that cannot be parsed.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class MalformedUrlException extends InvalidArgumentException {
    
    public function __construct(string $url) {
        parent::__construct("The URL '$url' could not be parsed.");
    }
}

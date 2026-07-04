<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;

/**
 * Exception type for the Farah page not found error condition.
 *
 * @author Daniel Schulz
 * @since 2018-04-01
 */
final class PageNotFoundException extends RuntimeException {
    
    public function __construct(string $missingPath) {
        parent::__construct("URL '$missingPath' could not be matched to an asset!");
    }
}


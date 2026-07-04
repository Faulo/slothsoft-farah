<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;

/**
 * Exception type for the Farah malformed document error condition.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class MalformedDocumentException extends RuntimeException {
    
    public function __construct(string $path) {
        parent::__construct("Asset document '$path' does not contain valid XML.");
    }
}

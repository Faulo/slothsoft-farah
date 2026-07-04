<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use SplFileInfo;

/**
 * Exception type for the Farah file not found error condition.
 *
 * @author Daniel Schulz
 * @since 2025-09-22
 */
final class FileNotFoundException extends RuntimeException {
    
    public function __construct(SplFileInfo $file) {
        parent::__construct("File '$file' does not exist.");
    }
}

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;

/**
 * Exception type for the Farah page redirection error condition.
 *
 * @author Daniel Schulz
 * @since 2018-04-01
 */
final class PageRedirectionException extends RuntimeException {
    
    private $targetPath;
    
    public function __construct(string $targetPath) {
        parent::__construct();
        $this->targetPath = $targetPath;
    }
    
    public function getTargetPath(): string {
        return $this->targetPath;
    }
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use Slothsoft\Farah\Module\Result\ResultStrategies;

/**
 * Exception type for HTTP download failures while resolving Farah executables.
 *
 * @author Daniel Schulz
 * @since 2018-06-27
 */
final class HttpDownloadExecutableException extends RuntimeException {
    
    private ResultStrategies $strategies;
    
    private bool $inline;
    
    public function __construct(ResultStrategies $strategies, bool $isInline = false) {
        parent::__construct();
        $this->strategies = $strategies;
        $this->inline = $isInline;
    }
    
    public function getExecutable(): ResultStrategies {
        return $this->strategies;
    }
    
    public function isInline(): bool {
        return $this->inline;
    }
}


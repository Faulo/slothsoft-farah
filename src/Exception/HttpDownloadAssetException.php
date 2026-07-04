<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;

/**
 * Exception type for HTTP download failures while resolving Farah assets.
 *
 * @author Daniel Schulz
 * @since 2018-06-27
 */
final class HttpDownloadAssetException extends RuntimeException {
    
    private ExecutableStrategies $strategies;
    
    private bool $inline;
    
    public function __construct(ExecutableStrategies $strategies, bool $isInline = false) {
        $this->strategies = $strategies;
        $this->inline = $isInline;
    }
    
    public function getStrategies(): ExecutableStrategies {
        return $this->strategies;
    }
    
    public function isInline(): bool {
        return $this->inline;
    }
}


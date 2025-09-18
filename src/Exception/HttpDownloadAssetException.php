<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Executable\ExecutableStrategies;

class HttpDownloadAssetException extends \RuntimeException {
    
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


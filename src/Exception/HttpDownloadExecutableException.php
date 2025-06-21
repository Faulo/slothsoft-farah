<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Result\ResultStrategies;

class HttpDownloadExecutableException extends \RuntimeException {

    private $strategies;

    private bool $isInline;

    public function __construct(ResultStrategies $strategies, bool $isInline = false) {
        $this->strategies = $strategies;
        $this->isInline = $isInline;
    }

    public function getExecutable(): ResultStrategies {
        return $this->strategies;
    }

    public function isInline(): bool {
        return $this->isInline;
    }
}


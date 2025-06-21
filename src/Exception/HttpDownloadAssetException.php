<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Executable\ExecutableStrategies;

class HttpDownloadAssetException extends \RuntimeException {

    private $strategies;

    private bool $isInline;

    public function __construct(ExecutableStrategies $strategies, bool $isInline = false) {
        $this->strategies = $strategies;
        $this->isInline = $isInline;
    }

    public function getStrategies() {
        return $this->strategies;
    }

    public function isInline(): bool {
        return $this->isInline;
    }
}


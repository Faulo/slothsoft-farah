<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Result\ResultInterface;

class HttpDownloadException extends \RuntimeException {

    private ResultInterface $result;

    private bool $inline;

    public function __construct(ResultInterface $result, bool $isInline = false) {
        $this->result = $result;
        $this->inline = $isInline;
    }

    public function getResult(): ResultInterface {
        return $this->result;
    }

    public function isInline(): bool {
        return $this->inline;
    }
}


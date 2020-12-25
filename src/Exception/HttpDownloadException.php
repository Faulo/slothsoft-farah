<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Result\ResultInterface;

class HttpDownloadException extends \RuntimeException {

    private $result;

    public function __construct(ResultInterface $result) {
        $this->result = $result;
    }

    public function getResult(): ResultInterface {
        return $this->result;
    }
}


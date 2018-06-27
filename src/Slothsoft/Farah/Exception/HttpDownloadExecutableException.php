<?php
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Result\ResultStrategies;

class HttpDownloadExecutableException extends \RuntimeException
{

    private $strategies;

    public function __construct(ResultStrategies $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getExecutable(): ResultStrategies
    {
        return $this->strategies;
    }
}


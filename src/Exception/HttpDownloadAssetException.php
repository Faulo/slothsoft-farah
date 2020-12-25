<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Executable\ExecutableStrategies;

class HttpDownloadAssetException extends \RuntimeException
{

    private $strategies;

    public function __construct(ExecutableStrategies $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getStrategies()
    {
        return $this->strategies;
    }
}


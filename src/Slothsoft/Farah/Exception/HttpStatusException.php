<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class HttpStatusException extends \RuntimeException
{

    public function __construct(int $code, string $message = '')
    {
        parent::__construct($message, $code);
    }
}


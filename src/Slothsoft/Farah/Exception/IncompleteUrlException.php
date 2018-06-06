<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class IncompleteUrlException extends \InvalidArgumentException
{

    public function __construct(string $url, string $missing)
    {
        parent::__construct("The URL '$url' is missing the $missing part.");
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class PageNotFoundException extends \RuntimeException
{

    public function __construct(string $missingPath)
    {
        parent::__construct("URL '$missingPath' could not be matched to an asset!");
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class EmptySitemapException extends \RuntimeException
{

    public function __construct(string $path)
    {
        parent::__construct("Sitemap document '$path' appears to be empty.");
    }
}

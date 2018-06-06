<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class MalformedDocumentException extends \RuntimeException
{

    public function __construct(string $path)
    {
        parent::__construct("Asset document '$path' does not contain valid XML.");
    }
}

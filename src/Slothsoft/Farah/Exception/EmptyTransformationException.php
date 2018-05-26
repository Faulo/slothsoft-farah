<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class EmptyTransformationException extends \RuntimeException
{

    public function __construct()
    {
        parent::__construct("Template transformation resulted in an empty document!");
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class StreamTypeNotSupportedException extends \InvalidArgumentException {

    public function __construct(string $type) {
        parent::__construct("Stream type '#$type' is not supported by this implementation.");
    }
}


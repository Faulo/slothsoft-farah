<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;
class ResultTypeNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $type) {
        parent::__construct("Result type '$type' is not supported by this implementation.");
    }
}


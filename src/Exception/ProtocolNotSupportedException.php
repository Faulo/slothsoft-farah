<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;
class ProtocolNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $protocol) {
        parent::__construct("URL protocol '$protocol' is not supported by this implementation.");
    }
}


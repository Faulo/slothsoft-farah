<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;
class NamespaceNotSupportedException extends InvalidArgumentException {
    
    public function __construct(string $namespace) {
        parent::__construct("XML namespace '$namespace' is not supported by this implementation.");
    }
}


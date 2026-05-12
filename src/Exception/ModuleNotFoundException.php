<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;
class ModuleNotFoundException extends InvalidArgumentException {
    
    public function __construct(string $id) {
        parent::__construct("There is no module '$id'.");
    }
}



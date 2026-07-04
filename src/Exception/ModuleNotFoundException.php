<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;

/**
 * Exception type for the Farah module not found error condition.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class ModuleNotFoundException extends InvalidArgumentException {
    
    public function __construct(string $id) {
        parent::__construct("There is no module '$id'.");
    }
}



<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

use Slothsoft\Core\IO\Sanitizer\SanitizerInterface;

interface ParameterFilterStrategyInterface {
    
    public function isAllowedName(string $name): bool;
    
    /**
     * @return iterable<string, SanitizerInterface>
     */
    public function getValueSanitizers(): iterable;
}


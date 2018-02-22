<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\ParameterFilters;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ParameterFilterInterface
{

    public function isAllowedName(string $name): bool;
}


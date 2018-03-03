<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\ParameterFilters;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AllowAllFilter implements ParameterFilterInterface
{

    public function isAllowedName(string $name): bool
    {
        return true;
    }
}


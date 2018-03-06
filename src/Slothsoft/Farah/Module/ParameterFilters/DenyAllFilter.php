<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\ParameterFilters;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DenyAllFilter implements ParameterFilterInterface
{

    public function isAllowedName(string $name): bool
    {
        return false;
    }

    public function getDefaultMap(): array
    {
        return [];
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\ParameterFilters;

class MapFilter implements ParameterFilterInterface
{

    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function isAllowedName(string $name): bool
    {
        return isset($this->map[$name]);
    }

    public function getDefaultMap(): array
    {
        return $this->map;
    }
}


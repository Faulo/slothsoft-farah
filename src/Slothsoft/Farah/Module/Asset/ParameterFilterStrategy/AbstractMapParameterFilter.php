<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

abstract class AbstractMapParameterFilter implements ParameterFilterStrategyInterface
{

    abstract protected function loadMap(): array;

    private $map;

    public function __construct()
    {
        $this->map = $this->loadMap();
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


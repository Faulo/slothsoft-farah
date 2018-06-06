<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterFilterStrategy;

interface ParameterFilterStrategyInterface
{

    public function isAllowedName(string $name): bool;

    public function getDefaultMap(): array;
}


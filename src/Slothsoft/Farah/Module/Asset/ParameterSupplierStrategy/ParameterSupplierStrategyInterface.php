<?php
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

interface ParameterSupplierStrategyInterface
{
    public function supplyParameters(AssetInterface $context) : iterable;
}


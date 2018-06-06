<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class NullParameterSupplier implements ParameterSupplierStrategyInterface
{
    public function supplyParameters(AssetInterface $context): iterable
    {
        return [];
    }
}


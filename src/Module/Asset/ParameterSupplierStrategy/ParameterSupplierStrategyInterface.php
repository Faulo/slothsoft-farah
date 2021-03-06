<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

interface ParameterSupplierStrategyInterface {

    public function supplyParameters(AssetInterface $context): iterable;
}


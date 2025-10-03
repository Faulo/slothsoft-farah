<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class FromReferenceParameterSupplier implements ParameterSupplierStrategyInterface {
    
    public function supplyParameters(AssetInterface $context): iterable {
        $url = $context->createRealUrl();
        return $url->getArguments();
    }
}


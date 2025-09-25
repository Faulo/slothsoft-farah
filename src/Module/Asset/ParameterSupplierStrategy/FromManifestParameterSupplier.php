<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

class FromManifestParameterSupplier implements ParameterSupplierStrategyInterface {
    
    public function supplyParameters(AssetInterface $context): iterable {
        $element = $context->getManifestElement();
        yield $element->getAttribute(Manifest::ATTR_PARAM_KEY) => $element->getAttribute(Manifest::ATTR_PARAM_VAL);
    }
}


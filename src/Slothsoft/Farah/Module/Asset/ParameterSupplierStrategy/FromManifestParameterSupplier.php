<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class FromManifestParameterSupplier implements ParameterSupplierStrategyInterface
{
    public function supplyParameters(AssetInterface $context): iterable
    {
        $element = $context->getManifestElement();
        yield $element->getAttribute('name') => $element->getAttribute('value');
    }
}


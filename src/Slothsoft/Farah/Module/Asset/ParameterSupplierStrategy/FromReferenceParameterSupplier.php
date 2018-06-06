<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;

class FromReferenceParameterSupplier implements ParameterSupplierStrategyInterface
{
    public function supplyParameters(AssetInterface $context): iterable
    {
        $element = $context->getManifestElement();
        $url = FarahUrl::createFromReference($element->getAttribute('ref'), $context->createUrl());
        return $url->getArguments();
    }
}


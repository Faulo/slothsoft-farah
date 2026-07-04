<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Parameter supplier strategy that forwards parameters from a referenced asset.
 *
 * @author Daniel Schulz
 * @since 2018-05-29
 */
final class FromReferenceParameterSupplier implements ParameterSupplierStrategyInterface {
    
    public function supplyParameters(AssetInterface $context): iterable {
        $url = $context->createRealUrl();
        return $url->getArguments();
    }
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

/**
 * Parameter supplier strategy that reads parameters from asset attributes.
 *
 * @author Daniel Schulz
 * @since 2018-05-29
 */
final class FromAttributesParameterSupplier implements ParameterSupplierStrategyInterface {
    
    public function supplyParameters(AssetInterface $context): iterable {
        return $context->getManifestElement()->getAttributes();
    }
}


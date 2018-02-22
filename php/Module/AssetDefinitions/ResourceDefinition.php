<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\Module\ParameterFilters\DenyAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDefinition extends GenericAssetDefinition
{

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return new DenyAllFilter();
    }
}


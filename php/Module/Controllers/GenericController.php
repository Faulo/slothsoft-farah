<?php
namespace Slothsoft\Farah\Module\Controllers;

use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericController implements ControllerInterface
{
    private $asset;
    
    public function setAsset(AssetInterface $asset) {
        $this->asset = $asset;
    }
    public function getAsset() : AssetInterface {
        return $this->asset;
    }
    public function createParameterFilter(): ParameterFilterInterface
    {
        return new AllowAllFilter();
    }
    public function createPathResolver() : PathResolverInterface
    {
        return PathResolverCatalog::createNullPathResolver($this->asset);
    }
    public function createResult(FarahUrl $url) : ResultInterface {
        return NullResult($url);
    }
    
    
}


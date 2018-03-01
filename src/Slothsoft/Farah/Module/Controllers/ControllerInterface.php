<?php
namespace Slothsoft\Farah\Module\Controllers;

use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ControllerInterface
{    
    public function setAsset(AssetInterface $asset);
    public function getAsset() : AssetInterface;
    
    public function createResult(FarahUrl $url) : ResultInterface;
    
    public function createPathResolver() : PathResolverInterface;
}


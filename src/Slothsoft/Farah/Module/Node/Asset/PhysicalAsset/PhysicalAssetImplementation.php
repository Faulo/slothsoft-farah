<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhysicalAssetImplementation extends AssetImplementation implements PhysicalAssetInterface
{
    
    public function getPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_PATH);
    }
    
    public function getRealPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_REALPATH);
    }
}


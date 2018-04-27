<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetBase;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhysicalAssetBase extends AssetBase implements PhysicalAssetInterface
{

    public final function getPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_PATH);
    }

    public final function getRealPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_REALPATH);
    }

    public function isDirectory() : bool
    {
        return is_dir($this->getRealPath());
    }
    public function isFile() : bool
    {
        return is_file($this->getRealPath());
    }

}


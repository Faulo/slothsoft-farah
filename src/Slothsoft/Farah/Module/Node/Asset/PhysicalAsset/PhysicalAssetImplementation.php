<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\FileWriterStringFromFileTrait;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhysicalAssetImplementation extends AssetImplementation implements PhysicalAssetInterface
{
    use FileWriterStringFromFileTrait;

    public function getPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_PATH);
    }

    public function getRealPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_REALPATH);
    }
    public function toFile() : HTTPFile
    {
        return HTTPFile::createFromPath($this->getRealPath());
    }

}


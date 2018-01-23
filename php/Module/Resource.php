<?php
namespace Slothsoft\Farah\Module;

use DOMDocument;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetUses\FileWriter;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Resource extends GenericAsset
implements FileWriter
{
    
    public function toFile() : HTTPFile
    {
        return HTTPFile::createFromPath($this->getRealPath());
    }
    
    public function toString() : string
    {
        return file_get_contents($this->getRealPath());
    }
}


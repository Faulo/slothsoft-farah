<?php
namespace Slothsoft\Farah\Module\AssetUses;

use Slothsoft\Farah\HTTPFile;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface FileWriter
{
    public function toFile() : HTTPFile;
    
    public function toString() : string;
}


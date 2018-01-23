<?php
namespace Slothsoft\Farah\Module\AssetUses;

use Slothsoft\Farah\HTTPFile;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface FileReader
{
    public function fromFile(HTTPFile $sourceFile);
    
    public function fromString(string $sourceString);
}


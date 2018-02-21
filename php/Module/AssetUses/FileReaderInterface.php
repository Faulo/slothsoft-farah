<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetUses;

use Slothsoft\Farah\HTTPFile;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface FileReaderInterface
{

    public function fromFile(HTTPFile $sourceFile);

    public function fromString(string $sourceString);
}


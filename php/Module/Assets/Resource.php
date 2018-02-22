<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Resource extends GenericAsset implements FileWriterInterface
{

    public function toFile(): HTTPFile
    {
        return HTTPFile::createFromPath($this->getRealPath());
    }

    public function toString(): string
    {
        return file_get_contents($this->getRealPath());
    }

    public function exists(): bool
    {
        return is_file($this->getRealPath());
    }
}


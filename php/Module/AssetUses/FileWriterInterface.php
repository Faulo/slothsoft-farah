<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetUses;

use Slothsoft\Farah\HTTPFile;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface FileWriterInterface
{

    public function toFile(): HTTPFile;

    public function toString(): string;
}


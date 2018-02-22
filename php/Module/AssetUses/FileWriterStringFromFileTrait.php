<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetUses;

/**
 *
 * @author Daniel Schulz
 *        
 */
trait FileWriterStringFromFileTrait {

    public function toString(): string
    {
        return $this->toFile()->getContents();
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\ModuleNodeBase;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class MetaBase extends ModuleNodeBase implements MetaInterface
{
    public function isImport() : bool {
        return false;
    }
    
    public function isParameter() : bool {
        return false;
    }

    public function isUseDocument(): bool
    {
        return false;
    }

    public function isUseTemplate(): bool
    {
        return false;
    }

    public function isUseManifest(): bool
    {
        return false;
    }

    public function isUseStylesheet(): bool
    {
        return false;
    }

    public function isUseScript(): bool
    {
        return false;
    }
}

 
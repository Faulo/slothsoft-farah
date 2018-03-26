<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\ModuleNodeImplementation;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MetaImplementation extends ModuleNodeImplementation implements MetaInterface
{

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

 
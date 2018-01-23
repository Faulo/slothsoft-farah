<?php
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\HTTPClosure;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ClosurableDefinition
{
    public function getClosure() : HTTPClosure;
}


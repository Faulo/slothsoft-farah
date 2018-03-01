<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ModuleElementFactoryInterface 
{
    public function create(ModuleElementCreator $ownerCreator, Module $ownerModule, LeanElement $element, LeanElement $parent = null);
}


<?php
namespace Slothsoft\Farah\Module\Element\Meta;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Element\ModuleElementInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface MetaInterface extends ModuleElementInterface
{
    public function initMeta(Module $ownerModule, LeanElement $element, array $children);
}


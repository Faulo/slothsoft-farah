<?php
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeFactory;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MetaFactory extends ModuleNodeFactory
{
    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        
    }
    protected function instantiateNode(LeanElement $element): MetaInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_SOURCE:
            case Module::TAG_OPTIONS:
                return new MetaImplementation();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}


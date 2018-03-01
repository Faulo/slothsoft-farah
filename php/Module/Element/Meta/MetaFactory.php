<?php
namespace Slothsoft\Farah\Module\Element\Meta;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Element\ModuleElementCreator;
use Slothsoft\Farah\Module\Element\ModuleElementFactoryInterface;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MetaFactory implements ModuleElementFactoryInterface
{
    public function create(ModuleElementCreator $ownerCreator, Module $ownerModule, LeanElement $element, LeanElement $parent = null) : MetaInterface
    {
        $meta = $this->instantiateMeta($element);
        $meta->initMeta(
            $ownerModule,
            $element,
            $ownerCreator->createList($ownerModule, $element->getChildren(), $element)
        );
        return $meta;
    }
    private function instantiateMeta(LeanElement $element): MetaInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_PARAM:
                return new ParamMeta();
            default:
                return new MetaImplementation();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}


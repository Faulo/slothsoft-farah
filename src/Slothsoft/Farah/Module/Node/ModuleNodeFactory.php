<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class ModuleNodeFactory
{

    public function create(ModuleNodeCreator $ownerCreator, Module $ownerModule, LeanElement $element, LeanElement $parent = null): ModuleNodeInterface
    {
        $this->normalizeElementAttributes($element, $parent);
        $node = $this->instantiateNode($element);
        $node->initModuleNode($ownerModule, $element, $ownerCreator->createList($ownerModule, $element->getChildren(), $element));
        return $node;
    }

    abstract protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null);

    abstract protected function instantiateNode(LeanElement $element): ModuleNodeInterface;
}


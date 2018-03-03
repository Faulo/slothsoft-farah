<?php
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Instruction\ParameterInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleNodeImplementation implements ModuleNodeInterface
{

    private $ownerModule;

    private $element;

    private $children;

    private $manifestArguments;

    public function initModuleNode(Module $ownerModule, LeanElement $element, array $children)
    {
        $this->ownerModule = $ownerModule;
        $this->element = $element;
        $this->children = $children;
    }

    public function getOwnerModule(): Module
    {
        return $this->ownerModule;
    }

    public function getElement(): LeanElement
    {
        return $this->element;
    }

    public function getElementTag(): string
    {
        return $this->element->getTag();
    }

    public function getElementAttribute(string $key, string $default = null): string
    {
        return $this->element->getAttribute($key, $default);
    }

    public function hasElementAttribute(string $key): bool
    {
        return $this->element->hasAttribute($key);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChildElement(LeanElement $element): ModuleNodeInterface
    {
        $element = $this->createChildElement($element);
        $this->children[] = $element;
        return $element;
    }

    private function createChildElement(LeanElement $element): ModuleNodeInterface
    {
        return $this->ownerModule->createModuleNode($element, $this->getElement());
    }

    public function mergeWithManifestArguments(FarahUrlArguments $args): FarahUrlArguments
    {
        return FarahUrlArguments::createFromMany($args, $this->getManifestArguments());
    }

    public function getManifestArguments(): FarahUrlArguments
    {
        if ($this->manifestArguments === null) {
            $this->manifestArguments = $this->loadManifestArguments();
        }
        return $this->manifestArguments;
    }

    protected function loadManifestArguments(): FarahUrlArguments
    {
        $data = [];
        foreach ($this->children as $child) {
            if ($child instanceof ParameterInstruction) {
                $data[$child->getName()] = $child->getValue();
            }
        }
        return FarahUrlArguments::createFromValueList($data);
    }

    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener)
    {
        foreach ($this->children as $child) {
            $child->crawlAndFireAppropriateEvents($listener);
        }
    }

    public function __toString(): string
    {
        return $this->element->getTag();
    }
}


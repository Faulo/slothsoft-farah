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
abstract class ModuleNodeBase implements ModuleNodeInterface
{

    private $ownerModule;

    private $element;

    private $children;

    private $manifestArguments;

    public function init(Module $ownerModule, LeanElement $element)
    {
        if ($this->ownerModule) {
            throw new \BadMethodCallException("Cannot initialize ModuleNode more than once.");
        }
        $this->ownerModule = $ownerModule;
        $this->element = $element;
    }

    protected final function getOwnerModule(): Module
    {
        return $this->ownerModule;
    }

    protected final function getElement(): LeanElement
    {
        return $this->element;
    }

    public final function getElementTag(): string
    {
        return $this->element->getTag();
    }

    protected final function getElementAttribute(string $key, string $default = null): string
    {
        return $this->element->getAttribute($key, $default);
    }

    protected final function hasElementAttribute(string $key): bool
    {
        return $this->element->hasAttribute($key);
    }

    public final function getChildren(): array
    {
        if ($this->children === null) {
            $this->children = $this->loadChildren();
        }
        return $this->children;
    }

    protected function loadChildren(): array
    {
        $ret = [];
        foreach ($this->getElement()->getChildren() as $element) {
            $node = $this->createChildNode($element);
            if ($node->isImport()) {
                foreach ($node->getImportNodes() as $referencedNode) {
                    $ret[] = $referencedNode;
                }
            } else {
                $ret[] = $node;
            }
        }
        return $ret;
    }

    public final function createChildNode(LeanElement $element): ModuleNodeInterface
    {
        return ModuleNodeCreator::getInstance()->create($this->ownerModule, $element, $this->getElement());
    }

    public function __toString(): string
    {
        return $this->element->getTag();
    }
}


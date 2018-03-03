<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeFactory;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use DomainException;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetFactory extends ModuleNodeFactory
{

    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        if (! $element->hasAttribute(Module::ATTR_NAME)) {
            throw new RuntimeException('Asset must be supplied with a name.');
        }
        if (! $element->hasAttribute(Module::ATTR_ASSETPATH)) {
            if (! $parent) {
                throw new RuntimeException('Asset must be supplied with either parent Asset or realpath+assetpath.');
            }
            $element->setAttribute(Module::ATTR_ASSETPATH, $this->inventElementAssetPath($element, $parent));
        }
    }

    private function inventElementAssetPath(LeanElement $element, LeanElement $parent): string
    {
        return $parent->getAttribute(Module::ATTR_ASSETPATH) . '/' . $element->getAttribute(Module::ATTR_NAME);
    }

    protected function instantiateNode(LeanElement $element): ModuleNodeInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_CONTAINER:
                return new ContainerAsset();
            case Module::TAG_FRAGMENT:
                return new FragmentAsset();
            case Module::TAG_CONTROLLER:
                return new ControllerAsset();
            case Module::TAG_CLOSURE:
                return new ClosureAsset();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}


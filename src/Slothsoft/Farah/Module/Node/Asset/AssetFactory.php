<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\TagNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeFactory;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetFactory extends ModuleNodeFactory
{

    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        assert($element->hasAttribute(Module::ATTR_NAME), 'Asset must be supplied with a name.');
        if (! $element->hasAttribute(Module::ATTR_ASSETPATH)) {
            assert($parent, 'Asset must be supplied with either parent element or assetpath attribute.');
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
            case Module::TAG_CLOSURE:
                return new ClosureAsset();
            case Module::TAG_EXTERNAL_DOCUMENT:
                return new ExternalDocumentAsset();
            case Module::TAG_CUSTOM_ASSET:
                $className = $element->getAttribute('class');
                return new $className();
        }
        throw new TagNotSupportedException(DOMHelper::NS_FARAH_MODULE, $element->getTag());
    }
}


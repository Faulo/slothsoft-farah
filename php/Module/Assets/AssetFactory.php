<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Element\ModuleElementCreator;
use Slothsoft\Farah\Module\Element\ModuleElementFactoryInterface;
use Slothsoft\Farah\Module\Element\ModuleElementInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use DomainException;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetFactory implements ModuleElementFactoryInterface
{
    
    public function create(ModuleElementCreator $ownerCreator, Module $ownerModule, LeanElement $element, LeanElement $parent = null) : ModuleElementInterface
    {
        $this->normalizeElementAttributes($element, $parent);
        $asset = $this->instantiateAsset($element);
        $asset->initAsset(
            $ownerModule,
            $element,
            $ownerCreator->createList($ownerModule, $element->getChildren(), $element),
            FarahUrlPath::createFromString($element->getAttribute('assetpath'))
        );
        return $asset;
    }

    private function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        if (! $element->hasAttribute(Module::ATTR_NAME)) {
            $element->setAttribute(Module::ATTR_NAME, $this->inventElementName($element));
        }
        if (! $element->hasAttribute(Module::ATTR_PATH)) {
            $element->setAttribute(Module::ATTR_PATH, $this->inventElementPath($element));
        }
        if (! $element->hasAttribute(Module::ATTR_REALPATH)) {
            if (! $parent) {
                throw new RuntimeException('Asset must be supplied with either parent Asset or realpath+assetpath.');
            }
            $element->setAttribute(Module::ATTR_REALPATH, $this->inventElementRealPath($element, $parent));
        }
        if (! $element->hasAttribute(Module::ATTR_ASSETPATH)) {
            if (! $parent) {
                throw new RuntimeException('Asset must be supplied with either parent Asset or realpath+assetpath.');
            }
            $element->setAttribute(Module::ATTR_ASSETPATH, $this->inventElementAssetPath($element, $parent));
        }
    }

    private function inventElementName(LeanElement $element): string
    {
        return $element->getTag() . '_' . spl_object_hash($element);
    }

    private function inventElementPath(LeanElement $element): string
    {
        $path = $element->getAttribute(Module::ATTR_NAME);
        if ($element->hasAttribute(Module::ATTR_TYPE)) {
            if ($extension = MimeTypeDictionary::guessExtension($element->getAttribute(Module::ATTR_TYPE))) {
                $path .= '.' . $extension;
            }
        }
        return $path;
    }

    private function inventElementRealPath(LeanElement $element, LeanElement $parent): string
    {
        return $parent->getAttribute(Module::ATTR_REALPATH) . DIRECTORY_SEPARATOR . $element->getAttribute(Module::ATTR_PATH);
    }

    private function inventElementAssetPath(LeanElement $element, LeanElement $parent): string
    {
        return $parent->getAttribute(Module::ATTR_ASSETPATH) . '/' . $element->getAttribute(Module::ATTR_NAME);
    }

    protected function instantiateAsset(LeanElement $element): AssetInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_ASSET_ROOT:
            case Module::TAG_DIRECTORY:
                return new ContainerAsset();
            case Module::TAG_FRAGMENT:
                return new FragmentAsset();
            case Module::TAG_RESOURCE_DIRECTORY:
                return new ResourceDirectoryAsset();
            case Module::TAG_CALL_CONTROLLER:
                return new CallControllerAsset();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}


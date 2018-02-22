<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\AssetDefinitions\ClosureDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\ContainerDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\ExecutableDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\GenericAssetDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\IncludeDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\ResourceDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\ResourceDirectoryDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\UnknownDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\UseDefinition;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DefinitionFactory
{

    private $ownerModule;

    public function __construct(Module $ownerModule)
    {
        $this->ownerModule = $ownerModule;
    }

    public function createDefinition(LeanElement $element, LeanElement $parent = null)
    {
        $this->normalizeElementAttributes($element, $parent);
        $definition = $this->isResourceDefinition($element) ? $this->instantiateResourceDefinition($element->getAttribute('type')) : $this->instantiateDefinition($element->getTag());
        $definition->init($this->ownerModule, $element, $this->createDefinitionList($element->getChildren(), $element));
        return $definition;
    }

    public function createDefinitionList(array $elementList, LeanElement $parent = null)
    {
        $ret = [];
        foreach ($elementList as $element) {
            $ret[] = $this->createDefinition($element, $parent);
        }
        return $ret;
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
                throw new RuntimeException('AssetDefinition must be supplied with either parent definition or realpath+assetpath.');
            }
            $element->setAttribute(Module::ATTR_REALPATH, $this->inventElementRealPath($element, $parent));
        }
        if (! $element->hasAttribute(Module::ATTR_ASSETPATH)) {
            if (! $parent) {
                throw new RuntimeException('AssetDefinition must be supplied with either parent definition or realpath+assetpath.');
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

    private function isResourceDefinition(LeanElement $element)
    {
        return $element->getTag() === Module::TAG_RESOURCE;
    }

    private function instantiateDefinition(string $tag): AssetDefinitionInterface
    {
        switch ($tag) {
            case Module::TAG_ASSET_ROOT:
            case Module::TAG_DIRECTORY:
            case Module::TAG_FRAGMENT:
                return new ContainerDefinition();
            case Module::TAG_RESOURCE_DIRECTORY:
                return new ResourceDirectoryDefinition();
            case Module::TAG_CLOSURE:
                return new ClosureDefinition();
            case Module::TAG_USE_DOCUMENT:
            case Module::TAG_USE_TEMPLATE:
            case Module::TAG_USE_STYLESHEET:
            case Module::TAG_USE_SCRIPT:
                return new UseDefinition();
            case Module::TAG_INCLUDE_FRAGMENT:
                return new IncludeDefinition();
            case Module::TAG_PARAM:
                return new GenericAssetDefinition();
            default:
                return new UnknownDefinition();
        }
    }

    private function instantiateResourceDefinition(string $type): AssetDefinitionInterface
    {
        switch ($type) {
            case 'application/x-php':
                return new ExecutableDefinition();
            default:
                return new ResourceDefinition();
        }
    }
}


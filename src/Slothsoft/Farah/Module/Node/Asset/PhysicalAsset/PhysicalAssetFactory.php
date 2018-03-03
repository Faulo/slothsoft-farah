<?php
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetFactory;
use DomainException;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhysicalAssetFactory extends AssetFactory
{
    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        parent::normalizeElementAttributes($element, $parent);
        
        if (! $element->hasAttribute(Module::ATTR_PATH)) {
            $element->setAttribute(Module::ATTR_PATH, $this->inventElementPath($element));
        }
        if (! $element->hasAttribute(Module::ATTR_REALPATH)) {
            if (! $parent) {
                throw new RuntimeException('Physical asset must be supplied with either parent Asset or realpath.');
            }
            $element->setAttribute(Module::ATTR_REALPATH, $this->inventElementRealPath($element, $parent));
        }
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
    
    
    
    protected function instantiateAsset(LeanElement $element): ModuleNodeInterface 
    {
        switch ($element->getTag()) {
            case Module::TAG_ASSET_ROOT:
                return new RootAsset();
            case Module::TAG_DIRECTORY:
                return new DirectoryAsset();
            case Module::TAG_RESOURCE_DIRECTORY:
                return new ResourceDirectoryAsset();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}


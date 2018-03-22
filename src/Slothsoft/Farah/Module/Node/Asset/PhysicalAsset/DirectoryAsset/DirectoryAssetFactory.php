<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\DirectoryAsset;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\TagNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DirectoryAssetFactory extends PhysicalAssetFactory
{
    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        parent::normalizeElementAttributes($element, $parent);
        
        //assert(is_dir($element->getAttribute(Module::ATTR_REALPATH)), "Directory asset at path {$element->getAttribute(Module::ATTR_REALPATH)} does not exist.");
    }
    
    protected function inventElementPath(LeanElement $element): string
    {
        return $element->getAttribute(Module::ATTR_NAME);
    }

    protected function instantiateNode(LeanElement $element): ModuleNodeInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_DIRECTORY:
            case Module::TAG_ASSET_ROOT:
                return new DirectoryAssetImplementation();
            case Module::TAG_RESOURCE_DIRECTORY:
                return new ResourceDirectoryAsset();
        }
        
        throw new TagNotSupportedException(DOMHelper::NS_FARAH_MODULE, $element->getTag());
    }
}


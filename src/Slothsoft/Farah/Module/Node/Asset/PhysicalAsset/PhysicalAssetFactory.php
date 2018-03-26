<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class PhysicalAssetFactory extends AssetFactory
{

    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        parent::normalizeElementAttributes($element, $parent);
        
        if (! $element->hasAttribute(Module::ATTR_PATH)) {
            $element->setAttribute(Module::ATTR_PATH, $this->inventElementPath($element));
        }
        if (! $element->hasAttribute(Module::ATTR_REALPATH)) {
            assert($parent, 'Physical asset must be supplied with either parent element or realpath attribute.');
            $element->setAttribute(Module::ATTR_REALPATH, $this->inventElementRealPath($element, $parent));
        }
    }

    private function inventElementRealPath(LeanElement $element, LeanElement $parent): string
    {
        return $parent->getAttribute(Module::ATTR_REALPATH) . DIRECTORY_SEPARATOR . $element->getAttribute(Module::ATTR_PATH);
    }

    abstract protected function inventElementPath(LeanElement $element);
}


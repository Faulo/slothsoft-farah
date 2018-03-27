<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceFactory extends PhysicalAssetFactory
{

    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {
        parent::normalizeElementAttributes($element, $parent);
        
        // assert(is_file($element->getAttribute(Module::ATTR_REALPATH)), "Resource asset at path {$element->getAttribute(Module::ATTR_REALPATH)} does not exist.");
    }

    protected function inventElementPath(LeanElement $element): string
    {
        $path = $element->getAttribute(Module::ATTR_NAME);
        if ($extension = MimeTypeDictionary::guessExtension($element->getAttribute(Module::ATTR_TYPE))) {
            $path .= '.' . $extension;
        }
        return $path;
    }

    protected function instantiateNode(LeanElement $element): ModuleNodeInterface
    {
        switch ($element->getAttribute('type')) {
            case 'text/*':
            case 'text/plain':
            case 'text/csv':
            case 'text/css':
                return new TextResource();
            case 'text/html':
                return new HtmlResource();
            case 'image/svg+xml':
            case 'application/xhtml+xml':
            case 'application/rdf+xml':
            case 'application/xml':
            case 'application/xslt+xml':
                return new XmlResource();
            case 'application/x-php':
                return new PhpResource();
            default:
                return new ResourceImplementation();
        }
    }
}


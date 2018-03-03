<?php
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceFactory extends PhysicalAssetFactory
{

    protected function instantiateNode(LeanElement $element): ModuleNodeInterface
    {
        switch ($element->getAttribute('type')) {
            case 'text/*':
            case 'text/plain':
            case 'text/csv':
            case 'text/css':
                return new TextResource();
            case 'text/html':
                return new TextResource();
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


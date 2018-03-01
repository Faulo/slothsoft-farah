<?php
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Assets\AssetFactory;
use Slothsoft\Farah\Module\Assets\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceFactory extends AssetFactory
{
    protected function instantiateAsset(LeanElement $element): AssetInterface
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
                return new GenericResource();
        }
    }
}


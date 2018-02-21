<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\MimeTypeDictionary;
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
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DefinitionFactory
{

    public static function createFromElement(Module $ownerModule, DOMElement $element, AssetDefinitionInterface $parent = null): AssetDefinitionInterface
    {
        $tag = $element->localName;
        $attributes = [];
        foreach ($element->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }
        
        $definition = self::createFromArray($ownerModule, $tag, $attributes, $parent);
        
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $definition->appendChild(self::createFromElement($ownerModule, $child, $definition));
            }
        }
        
        return $definition;
    }

    public static function createFromArray(Module $ownerModule, string $tag, array $attributes, AssetDefinitionInterface $parent = null): AssetDefinitionInterface
    {
        $ret = null;
        switch ($tag) {
            case Module::TAG_ASSET_ROOT:
            case Module::TAG_DIRECTORY:
            case Module::TAG_FRAGMENT:
                $ret = new ContainerDefinition();
                break;
            case Module::TAG_RESOURCE:
                assert(isset($attributes['type']), "Asset of type <resource> requires type attribute.");
                
                switch ($attributes['type']) {
                    case 'application/x-php':
                        $ret = new ExecutableDefinition();
                        break;
                    default:
                        $ret = new ResourceDefinition();
                        break;
                }
                break;
            case Module::TAG_RESOURCE_DIRECTORY:
                $ret = new ResourceDirectoryDefinition();
                break;
            case Module::TAG_CLOSURE:
                $ret = new ClosureDefinition();
                break;
            case Module::TAG_USE_DOCUMENT:
            case Module::TAG_USE_TEMPLATE:
            case Module::TAG_USE_STYLESHEET:
            case Module::TAG_USE_SCRIPT:
                $ret = new UseDefinition();
                break;
            case Module::TAG_INCLUDE_FRAGMENT:
                $ret = new IncludeDefinition();
                break;
            case Module::TAG_PARAM:
                $ret = new GenericAssetDefinition();
                break;
            default:
                /*
                throw ExceptionContext::append(
                    new DomainException("Module tag <sfm:$tag> is not supported by this implementation."),
                    [
                        'definition' => $parent,
                    ]
                );
                //*/
                $ret = new UnknownDefinition();
                break;
        }
        
        if (! isset($attributes['name'])) {
            $attributes['name'] = $tag . '_' . spl_object_hash($ret);
        }
        if (! isset($attributes['path'])) {
            $attributes['path'] = $attributes['name'];
            if (isset($attributes['type'])) {
                if ($extension = MimeTypeDictionary::guessExtension($attributes['type'])) {
                    $attributes['path'] .= '.' . $extension;
                }
            }
        }
        if (! $parent) {
            assert(isset($attributes['realpath'], $attributes['assetpath']), 'AssetDefinition must be supplied with either parent definition or realpath+assetpath.');
        }
        if (! isset($attributes['realpath'])) {
            $attributes['realpath'] = $parent->getAttribute('realpath') . DIRECTORY_SEPARATOR . $attributes['path'];
        }
        if (! isset($attributes['assetpath'])) {
            $attributes['assetpath'] = $parent->getAttribute('assetpath') . '/' . $attributes['name'];
        }
        
        $ret->init($ownerModule, $tag, $attributes);
        
        return $ret;
    }
}


<?php
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use DOMNode;
use Slothsoft\Farah\Module\PathResolvers\NullPathResolver;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetDefinition
{
    public static function createFromElement(Module $ownerModule, DOMElement $element, AssetDefinition $parent = null) {
        $tag = $element->localName;
        $attributes = [];
        foreach ($element->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }
        
        $definition = self::createFromArray($ownerModule, $tag, $attributes, $parent);
        
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $child = self::createFromElement($ownerModule, $child, $definition);
                $definition->appendChild($child);
            }
        }        
        
        return $definition;
    }
    public static function createFromArray(Module $ownerModule, string $tag, array $attributes, AssetDefinition $parent = null) {
        
        
        
        $ret = null;
        switch ($tag) {
            case Module::TAG_ASSET_ROOT:
            case Module::TAG_DIRECTORY:
            case Module::TAG_FRAGMENT:
                $ret = new ContainerDefinition();
                break;
            case Module::TAG_RESOURCE:
                assert(isset($attributes['type']), "asset of type <resource> requires type attribute");
                
                switch ($attributes['type']) {
                    case 'application/x-php':
                        $ret = new ExecutableDefinition();
                        break;
                    default:
                        $ret = new AssetDefinition();
                        break;
                }
                break;
            case Module::TAG_RESOURCE_DIRECTORY:
                $ret = new ResourceDirectoryDefinition();
                break;
            case Module::TAG_CLOSURE:
                $ret = new ClosureDefinition();
                break;
            default:
                $ret = new AssetDefinition();
                break;
        }
        
        if (!isset($attributes['name'])) {
            $attributes['name'] = $tag.'_'.spl_object_hash($ret);
        }
        if (!isset($attributes['path'])) {
            $attributes['path'] = $attributes['name'];
            if (isset($attributes['type'])) {
                if ($extension = MimeTypeDictionary::guessExtension($attributes['type'])) {
                    $attributes['path'] .= '.' . $extension;
                }
            }
        }
        if (!$parent) {
            assert(isset($attributes['realpath'], $attributes['assetpath']), 'must supply either parent definition or realpath+assetpath');
        }
        if (!isset($attributes['realpath'])) {
            $attributes['realpath'] = $parent->getAttribute('realpath') . DIRECTORY_SEPARATOR . $attributes['path'];
        }
        if (!isset($attributes['assetpath'])) {
            $attributes['assetpath'] = $parent->getAttribute('assetpath') . '/' . $attributes['name'];
        }
        
        $ret->init($ownerModule, $tag, $attributes);
        
        return $ret;
    }
    
    private $ownerModule;
    private $tag;
    private $attributes;
    private $children;
    private $pathResolver;

    public function init(Module $ownerModule, string $tag, array $attributes)
    {
        $this->ownerModule = $ownerModule;
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = [];
        
        //echo "loading definition {$this->getId()}" . PHP_EOL;
    }
    
    public function getOwnerModule() : Module {
        return $this->ownerModule;
    }
    public function getTag() : string  {
        return $this->tag;
    }
    public function getId() : string {
        return $this->ownerModule->getId() . $this->getAssetPath();
    }
    public function getName() : string {
        return $this->attributes['name'] ?? '';
    }
    public function getRealPath() : string {
        return $this->attributes['realpath'] ?? '';
    }
    public function getAssetPath() : string {
        return $this->attributes['assetpath'] ?? '';
    }
    public function getAttribute(string $key) : string {
        return $this->attributes[$key] ?? '';
    }
    public function hasAttribute(string $key) : bool {
        return isset($this->attributes[$key]);
    }
    public function appendChild(AssetDefinition $asset) {
        $this->children[] = $asset;
    }
    public function getChildren() : array {
        return $this->children;
    }
    public function traverseTo(string $path) : AssetDefinition {
        return $this->getPathResolver()->resolvePath($path);
    }
    public function getPathResolver() : PathResolverInterface {
        if ($this->pathResolver === null) {
            $this->pathResolver = $this->loadPathResolver();
        }
        return $this->pathResolver;
    }
    protected function loadPathResolver() : PathResolverInterface {
        return new NullPathResolver($this);
    }
    
    public function toNode(DOMDocument $targetDoc) : DOMNode {
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $this->tag);
        foreach ($this->attributes as $key => $val) {
            $node->setAttribute($key, $val);
        }
        foreach ($this->getChildren() as $child) {
            $node->appendChild($child->toNode($targetDoc));
        }
        return $node;
    }
}


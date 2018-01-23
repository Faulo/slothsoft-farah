<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use Slothsoft\Farah\Module\Resources\HtmlResource;
use Slothsoft\Farah\Module\Resources\PhpResource;
use Slothsoft\Farah\Module\Resources\XmlResource;
use DOMElement;
use DomainException;
use UnexpectedValueException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Repository
{
    private $moduleList;
    private $assetCache;
    
    public static function getInstance() {
        static $instance;
        if ($instance === null) {
            $instance = new Repository();
        }
        return $instance;
    }
    private function __construct() {
        $this->moduleList = [];
        $this->assetCache = new AssetCache();
    }
    public function lookupModule(string $vendor, string $name) : Module {
        $key = "$vendor@$name";
        if (!isset($this->moduleList[$key])) {
            $this->moduleList[$key] = new Module($vendor, $name);
        }
        return $this->moduleList[$key];
    }
    public function lookupAsset(AssetUri $uri) {
        $cacheItem = $this->assetCache->getItem($uri);
        if (!$cacheItem->isHit()) {
            $module = $this->lookupModule($uri->getVendor(), $uri->getModule());
            $assetDefinition = $module->getAssetDefinition($uri->getPath());
            $asset = $this->instantiateAsset($assetDefinition, $module);
            $asset->setArguments($uri->getQueryArray());
            $cacheItem->set($asset);
        }
        return $cacheItem->get();
    }
    public function instantiateAsset(AssetDefinition $definition, Module $ownerModule) : AssetInterface {
        switch ($definition->getTag()) {
            case Module::TAG_ASSET_ROOT:
            case Module::TAG_DIRECTORY:
            case Module::TAG_RESOURCE_DIRECTORY:
                $ret = new GenericAsset();
                break;
            case Module::TAG_FRAGMENT:
                $ret = new Fragment();
                break;
            case Module::TAG_RESOURCE:
                $mimeType = $definition->getAttribute('type');
                $ret = $this->instantiateResource($mimeType);
                break;
            case Module::TAG_CLOSURE:
                $ret = new PhpResource();
                break;
            default:
                throw new UnexpectedValueException("unknown asset type <{$definition->getTag()}>");
        }
        $ret->init($definition);
        return $ret;
    }
    private function instantiateResource(string $mimeType) : Resource
    {
        //TODO: extract the class names from a config file or something
        switch ($mimeType) {
            case 'application/x-php':
                return new PhpResource();
            case 'text/html':
                return new HtmlResource();
            case 'image/svg+xml':
            case 'application/xhtml+xml':
            case 'application/rdf+xml':
            case 'application/xml':
            case 'application/xslt+xml':
                return new XmlResource();
            default:
                return new Resource();
        }
        throw new DomainException("unknown mime type $mimeType");
    }
}


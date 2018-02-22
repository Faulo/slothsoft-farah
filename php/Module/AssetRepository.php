<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\Assets\Directory;
use Slothsoft\Farah\Module\Assets\Fragment;
use Slothsoft\Farah\Module\Assets\GenericAsset;
use Slothsoft\Farah\Module\Assets\Resource;
use Slothsoft\Farah\Module\Assets\Resources\ExecutableResource;
use Slothsoft\Farah\Module\Assets\Resources\HtmlResource;
use Slothsoft\Farah\Module\Assets\Resources\TextResource;
use Slothsoft\Farah\Module\Assets\Resources\XmlResource;
use UnexpectedValueException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetRepository
{

    private $assetCache;

    public static function getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new AssetRepository();
        }
        return $instance;
    }

    private function __construct()
    {
        $this->assetCache = new AssetCache();
    }

    public function lookupAssetByUrl(FarahUrl $url): AssetInterface
    {
        $cacheItem = $this->assetCache->getItem($url);
        if (! $cacheItem->isHit()) {
            $cacheItem->set($this->createAssetByUrl($url));
            $this->assetCache->save($cacheItem);
        }
        return $cacheItem->get();
    }

    private function createAssetByUrl(FarahUrl $url): AssetInterface
    {
        $module = ModuleRepository::getInstance()->lookupModuleByUrl($url);
        $definition = $module->getAssetDefinition($url->getPath());
        $arguments = $url->getQueryArray();
        if ($definition->filterParameters($arguments)) {
            $url = $url->withQueryArray($arguments);
            $asset = $this->lookupAssetByUrl($url);
        } else {
            $asset = $this->isResourceDefinition($definition) ? $this->instantiateResourceAsset($definition->getElementAttribute(Module::ATTR_TYPE)) : $this->instantiateAsset($definition->getElementTag());
            $asset->addEventAncestor($module);
            $asset->init($definition, $url);
        }
        return $asset;
    }

    private function isResourceDefinition(AssetDefinitionInterface $definition)
    {
        return $definition->getElementTag() === Module::TAG_RESOURCE;
    }

    private function instantiateAsset(string $tag): AssetInterface
    {
        switch ($tag) {
            case Module::TAG_ASSET_ROOT:
                return new GenericAsset();
            case Module::TAG_FRAGMENT:
                return new Fragment();
            case Module::TAG_CLOSURE:
                return new ExecutableResource();
            case Module::TAG_DIRECTORY:
            case Module::TAG_RESOURCE_DIRECTORY:
                return new Directory();
            default:
                throw ExceptionContext::append(new UnexpectedValueException("Module tag <{$tag}> is is not supported by this implementation."), [
                    'definition' => $definition
                ]);
        }
    }

    private function instantiateResourceAsset(string $mimeType): Resource
    {
        // TODO: extract the class names from a config file or something
        switch ($mimeType) {
            case 'text/*':
            case 'text/plain':
            case 'text/csv':
            case 'text/css':
                return new TextResource();
            case 'application/x-php':
                return new ExecutableResource();
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
    }
}


<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Kernel;
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
use DomainException;
use Throwable;
use UnexpectedValueException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetRepository
{

    private $moduleList;

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
        $this->moduleList = [];
        $this->assetCache = new AssetCache();
    }

    public function lookupModule(string $vendor, string $name): Module
    {
        $key = "$vendor@$name";
        if (!isset($this->moduleList[$key])) {
            $this->moduleList[$key] = new Module($vendor, $name);
            try {
                $this->moduleList[$key]->addEventAncestor(Kernel::getInstance());     // @TODO: öhh
                $this->moduleList[$key]->init();
            } catch(Throwable $exception) {
                throw ExceptionContext::append(
                    $exception,
                    ['module' => $this->moduleList[$key]]
                );
            }
        }
        return $this->moduleList[$key];
    }

    public function lookupAsset(string $vendor, string $module, string $ref, array $args = []): AssetInterface
    {
        $module = $this->lookupModule($vendor, $module);
        $url = FarahUrl::createFromReference($ref, $module, $args);
        return $this->lookupAssetByUrl($url);
    }

    public function lookupAssetByUrl(FarahUrl $url): AssetInterface
    {
        $cacheItem = $this->assetCache->getItem($url);
        if (! $cacheItem->isHit()) {
            $module = $this->lookupModule($url->getVendor(), $url->getModule());
            $assetDefinition = $module->getAssetDefinition($url->getPath());
            $assetArguments = $url->getQueryArray();
            if ($assetDefinition->filterParameters($assetArguments)) {
                $url = $url->withQueryArray($assetArguments);
                $asset = $this->lookupAssetByUrl($url);
            } else {
                $asset = $this->instantiateAsset($assetDefinition, $url);
                $asset->addEventAncestor($module);
            }
            $cacheItem->set($asset);
            $this->assetCache->save($cacheItem);
        }
        return $cacheItem->get();
    }

    private function instantiateAsset(AssetDefinitionInterface $definition, FarahUrl $url): AssetInterface
    {
        switch ($definition->getTag()) {
            case Module::TAG_ASSET_ROOT:
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
                $ret = new ExecutableResource();
                break;
            case Module::TAG_DIRECTORY:
            case Module::TAG_RESOURCE_DIRECTORY:
                $ret = new Directory();
                break;
            default:
                throw ExceptionContext::append(
                    new UnexpectedValueException("Module tag <{$definition->getTag()}> is is not supported by this implementation."),
                    ['definition' => $definition]
                    );
        }
        $ret->init($definition, $url);
        return $ret;
    }

    private function instantiateResource(string $mimeType): Resource
    {
        // TODO: extract the class names from a config file or something
        switch ($mimeType) {
            case 'text/*':
            case 'text/plain':
            case 'text/csv':
            case 'text/css':
                return new TextResource();
                break;
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
        throw ExceptionContext::append(new DomainException("Mime type $mimeType is not supported by this implementation."));
    }
}


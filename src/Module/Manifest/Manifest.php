<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\Asset;
use Slothsoft\Farah\Module\Asset\AssetContainer;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use SplFileInfo;

class Manifest implements ManifestInterface {
    
    // asset tags
    public const TAG_FRAGMENT = 'fragment';
    
    public const TAG_CONTAINER = 'container';
    
    public const TAG_EXTERNAL_RESOURCE = 'external-resource';
    
    public const TAG_CUSTOM_ASSET = 'custom-asset';
    
    public const TAG_DAEMON = 'daemon';
    
    // runtime-only asset tags
    public const TAG_DOCUMENT = 'document';
    
    public const TAG_ERROR = 'error';
    
    public const TAG_CLOSURE = 'closure';
    
    public const TAG_REQUEST_INFO = 'request-info';
    
    // physical asset tags
    public const TAG_ASSET_ROOT = 'assets';
    
    public const TAG_RESOURCE = 'resource';
    
    public const TAG_DIRECTORY = 'directory';
    
    public const TAG_RESOURCE_DIRECTORY = 'resource-directory';
    
    public const TAG_MANIFEST_DIRECTORY = 'manifest-directory';
    
    public const TAG_TEMPLATE_RESOURCE = 'template-resource';
    
    // meta tags
    public const TAG_SOURCE = 'source';
    
    public const TAG_OPTIONS = 'options';
    
    public const TAG_PARAM = 'param';
    
    // instruction tags
    public const TAG_IMPORT = 'import';
    
    public const TAG_USE_DOCUMENT = 'use-document';
    
    public const TAG_USE_TEMPLATE = 'use-template';
    
    public const TAG_USE_MANIFEST = 'use-manifest';
    
    public const TAG_LINK_STYLESHEET = 'link-stylesheet';
    
    public const TAG_LINK_SCRIPT = 'link-script';
    
    public const TAG_LINK_MODULE = 'link-module';
    
    public const TAG_LINK_CONTENT = 'link-content';
    
    public const TAG_LINK_DICTIONARY = 'link-dictionary';
    
    // attributes
    public const ATTR_NAME = 'name';
    
    public const ATTR_ID = 'url';
    
    public const ATTR_HREF = 'href';
    
    public const ATTR_SRC = 'src';
    
    public const ATTR_ALIAS = 'as';
    
    public const ATTR_PATH = 'path';
    
    public const ATTR_TYPE = 'type';
    
    public const ATTR_REALPATH = 'realpath';
    
    public const ATTR_ASSETPATH = 'assetpath';
    
    public const ATTR_REFERENCE = 'ref';
    
    public const ATTR_IMPORT = 'import';
    
    public const ATTR_IMPORT_SELF = 'self';
    
    public const ATTR_IMPORT_CHILDREN = 'children';
    
    public const ATTR_USE = 'use';
    
    public const ATTR_URL = 'url';
    
    public const ATTR_USE_MANIFEST = 'manifest';
    
    public const ATTR_USE_DOCUMENT = 'document';
    
    public const ATTR_USE_TEMPLATE = 'template';
    
    public const ATTR_USE_STYLESHEET = 'stylesheet';
    
    public const ATTR_USE_SCRIPT = 'script';
    
    public const ATTR_USE_MODULE = 'module';
    
    public const ATTR_USE_CONTENT = 'content';
    
    public const ATTR_USE_DICTIONARY = 'dictionary';
    
    public const ATTR_EXECUTABLE_BUILDER = 'executable-builder';
    
    public const ATTR_PATH_RESOLVER = 'path-resolver';
    
    public const ATTR_PARAMETER_FILTER = 'parameter-filter';
    
    public const ATTR_PARAMETER_SUPPLIER = 'parameter-supplier';
    
    public const ATTR_INSTRUCTION = 'instruction';
    
    public const ATTR_PARAM_KEY = 'name';
    
    public const ATTR_PARAM_VAL = 'value';
    
    // params
    public const PARAM_LOAD = 'load';
    
    public const PARAM_LOAD_TREE = 'tree';
    
    public const PARAM_LOAD_CHILDREN = 'children';
    
    public const PARAM_INCLUDES = 'includes';
    
    public const PARAM_INCLUDES_EMBED = 'embed';
    
    // misc
    private const TEMPLATE_ERROR = 'slothsoft@farah/xsl/error';
    
    private const FILE_MANIFEST = 'manifest.xml';
    
    private Module $ownerKernel;
    
    private FarahUrlAuthority $authority;
    
    private string $manifestDirectory;
    
    private ManifestStrategies $strategies;
    
    private AssetContainer $assets;
    
    private ?LeanElement $rootElement = null;
    
    public function __construct(Module $ownerKernel, FarahUrlAuthority $authority, string $manifestDirectory, ManifestStrategies $strategies) {
        $this->ownerKernel = $ownerKernel;
        $this->authority = $authority;
        $this->manifestDirectory = $manifestDirectory;
        $this->strategies = $strategies;
        
        $this->assets = new AssetContainer();
    }
    
    public function getId(): string {
        return (string) $this->authority;
    }
    
    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl {
        return FarahUrl::createFromComponents($this->authority, $path, $args, $fragment);
    }
    
    public function lookupAsset($path): AssetInterface {
        if (is_string($path)) {
            $path = FarahUrlPath::createFromString($path);
        }
        return $this->lookupAssetTyped($path);
    }
    
    private function lookupAssetTyped(FarahUrlPath $path): AssetInterface {
        if ($this->assets->has($path)) {
            return $this->assets->get($path);
        }
        
        if ($path->isEmpty()) {
            $asset = $this->createAsset($this->getRootElement(), FarahUrlPath::createEmpty());
        } else {
            $parent = $this->lookupAssetTyped($path->withoutLastSegment());
            $element = $parent->getChildManifestElement($path->getName());
            $asset = $this->createAsset($element);
        }
        
        $this->assets->put($path, $asset);
        return $asset;
    }
    
    public function clearCachedAssets(): void {
        $this->assets->clear();
    }
    
    private function createAsset(LeanElement $element): AssetInterface {
        $strategies = $this->strategies->assetBuilder->buildAssetStrategies($this, $element);
        $asset = new Asset($this, $element, FarahUrlPath::createFromString($element->getAttribute(self::ATTR_ASSETPATH)), $strategies);
        return $asset;
    }
    
    public function createManifestFile(string $fileName): SplFileInfo {
        return new SplFileInfo($this->manifestDirectory . DIRECTORY_SEPARATOR . $fileName);
    }
    
    public function createCacheFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerKernel->createCachedFile($fileName, $this->createUrl($path, $args, $fragment));
    }
    
    public function createDataFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerKernel->createDataFile($fileName, $this->createUrl($path, $args, $fragment));
    }
    
    private function getRootElement(): LeanElement {
        if ($this->rootElement === null) {
            $this->rootElement = $this->strategies->treeLoader->loadTree($this);
        }
        return $this->rootElement;
    }
    
    public function normalizeManifestElement(LeanElement $parent, LeanElement $child): void {
        $this->strategies->assetBuilder->normalizeElement($child, $parent);
    }
    
    public function normalizeManifestTree(LeanElement $root): void {
        foreach ($this->getManifestAttributes() as $key => $val) {
            $root->setAttribute($key, $val);
        }
        $this->strategies->assetBuilder->normalizeElement($root);
    }
    
    private function getManifestAttributes(): array {
        return [
            static::ATTR_ID => $this->getId(),
            static::ATTR_NAME => $this->authority->getModule(),
            static::ATTR_ASSETPATH => '',
            static::ATTR_PATH => '',
            static::ATTR_REALPATH => $this->manifestDirectory
        ];
    }
}


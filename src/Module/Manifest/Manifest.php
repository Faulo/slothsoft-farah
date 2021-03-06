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
    const TAG_FRAGMENT = 'fragment';

    const TAG_CONTAINER = 'container';

    const TAG_EXTERNAL_RESOURCE = 'external-resource';

    const TAG_CUSTOM_ASSET = 'custom-asset';

    const TAG_DAEMON = 'daemon';

    // runtime-only asset tags
    const TAG_DOCUMENT = 'document';

    const TAG_ERROR = 'error';

    const TAG_CLOSURE = 'closure';

    // physical asset tags
    const TAG_ASSET_ROOT = 'assets';

    const TAG_RESOURCE = 'resource';

    const TAG_DIRECTORY = 'directory';

    const TAG_RESOURCE_DIRECTORY = 'resource-directory';

    const TAG_TEMPLATE_RESOURCE = 'template-resource';

    // meta tags
    const TAG_SOURCE = 'source';

    const TAG_OPTIONS = 'options';

    const TAG_PARAM = 'param';

    // instruction tags
    const TAG_IMPORT = 'import';

    const TAG_USE_DOCUMENT = 'use-document';

    const TAG_USE_TEMPLATE = 'use-template';

    const TAG_USE_MANIFEST = 'use-manifest';

    const TAG_LINK_STYLESHEET = 'link-stylesheet';

    const TAG_LINK_SCRIPT = 'link-script';

    const TAG_LINK_MODULE = 'link-module';

    // attributes
    const ATTR_NAME = 'name';

    const ATTR_ID = 'url';

    const ATTR_HREF = 'href';

    const ATTR_SRC = 'src';

    const ATTR_ALIAS = 'as';

    const ATTR_PATH = 'path';

    const ATTR_TYPE = 'type';

    const ATTR_REALPATH = 'realpath';

    const ATTR_ASSETPATH = 'assetpath';

    const ATTR_REFERENCE = 'ref';

    const ATTR_USE = 'use';

    const ATTR_USE_MANIFEST = 'manifest';

    const ATTR_USE_DOCUMENT = 'document';

    const ATTR_USE_TEMPLATE = 'template';

    const ATTR_USE_STYLESHEET = 'stylesheet';

    const ATTR_USE_SCRIPT = 'script';

    const ATTR_PARAM_KEY = 'name';

    const ATTR_PARAM_VAL = 'value';

    const EVENT_USE_DOCUMENT = 'use-document';

    const EVENT_USE_TEMPLATE = 'use-template';

    const EVENT_USE_SCRIPT = 'use-script';

    const EVENT_USE_STYLESHEET = 'use-stylesheet';

    const EVENT_SET_PARAMETER = 'set-parameter';

    const TEMPLATE_ERROR = 'slothsoft@farah/xsl/error';

    const FILE_MANIFEST = 'manifest.xml';

    private $ownerKernel;

    private $authority;

    private $manifestDirectory;

    private $strategies;

    private $assets;

    private $rootElement;

    private $rootAsset;

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
        if (! $this->assets->has($path)) {
            $this->assets->put($path, $this->getRootAsset()
                ->traverseTo((string) $path));
        }
        return $this->assets->get($path);
    }

    public function normalizeElement(LeanElement $parent, LeanElement $element): void {
        $this->strategies->assetBuilder->normalizeTree($parent, $element);
    }

    public function createAsset(LeanElement $element): AssetInterface {
        $strategies = $this->strategies->assetBuilder->buildAssetStrategies($this, $element);
        return new Asset($this, $element, FarahUrlPath::createFromString($element->getAttribute('assetpath')), $strategies);
    }

    public function createManifestFile(string $fileName): SplFileInfo {
        return new SplFileInfo($this->manifestDirectory . DIRECTORY_SEPARATOR . $fileName);
    }

    public function createCacheFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerKernel->createCachedFile($fileName, $this->createUrl($path, $args, $fragment));
    }

    public function createDataFile(string $fileName, $path = null, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerKernel->createCachedFile($fileName, $this->createUrl($path, $args, $fragment));
    }

    private function getRootAsset(): AssetInterface {
        if ($this->rootAsset === null) {
            $this->rootAsset = $this->createAsset($this->getRootElement(), FarahUrlPath::createEmpty());
        }
        return $this->rootAsset;
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


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Node\ModuleNodeCreator;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\XmlManifest;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Module
{

    const TAGS_ASSETS = [
        self::TAG_CONTAINER,
        self::TAG_FRAGMENT,
        self::TAG_CUSTOM_ASSET,
        self::TAG_EXTERNAL_RESOURCE,
        self::TAG_CLOSURE,
        self::TAG_DIRECTORY,
        self::TAG_RESOURCE_DIRECTORY,
        self::TAG_ASSET_ROOT,
        self::TAG_RESOURCE
    ];

    // asset tags
    const TAG_FRAGMENT = 'fragment';

    const TAG_CONTAINER = 'container';

    const TAG_EXTERNAL_RESOURCE = 'external-resource';

    const TAG_CUSTOM_ASSET = 'custom-asset';

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

    const TAG_LINK_STYLESHEET = 'link-stylesheet';

    const TAG_LINK_SCRIPT = 'link-script';

    const TAG_USE_TEMPLATE = 'use-template';

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

    private $authority;

    private $assetDirectory;

    private $assetManifest;

    private $rootAsset;

    private $assetList = [];

    // TODO: AssetCache
    public function __construct(FarahUrlAuthority $authority, string $assetDirectory)
    {
        $this->authority = $authority;
        $this->assetDirectory = $assetDirectory;
        $this->assetManifest = new XmlManifest($this->assetDirectory . DIRECTORY_SEPARATOR . self::FILE_MANIFEST);
    }

    public function getAuthority(): FarahUrlAuthority
    {
        return $this->authority;
    }

    public function getManifest(): ManifestInterface
    {
        return $this->assetManifest;
    }

    public function getAssetDirectory(): string
    {
        return $this->assetDirectory;
    }

    public function getId(): string
    {
        return (string) $this->authority;
    }

    public function lookupAssetByPath(FarahUrlPath $path): AssetInterface
    {
        $id = (string) $path;
        if (! isset($this->assetList[$id])) {
            $this->assetList[$id] = $this->getRootAsset()->traverseTo($id);
        }
        return $this->assetList[$id];
    }

    public function getRootAsset(): AssetInterface
    {
        if ($this->rootAsset === null) {
            $this->rootAsset = $this->loadRootAsset();
        }
        return $this->rootAsset;
    }

    private function loadRootAsset(): AssetInterface
    {
        $manifestElemet = $this->assetManifest->getRootElement();
        $manifestElemet->setAttribute('realpath', $this->assetDirectory);
        $manifestElemet->setAttribute('name', $this->authority->getModule());
        $manifestElemet->setAttribute('assetpath', '');
        
        return ModuleNodeCreator::getInstance()->create($this, $manifestElemet);
    }

    public function createUrl(FarahUrlPath $path, FarahUrlArguments $args): FarahUrl
    {
        return FarahUrl::createFromComponents($this->authority, $path, $args);
    }

    public function createModuleNode(LeanElement $element, LeanElement $parent): ModuleNodeInterface
    {
        return ModuleNodeCreator::getInstance()->create($this, $element, $parent);
    }
}


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

/**
 *
 * @author Daniel Schulz
 *        
 */
class Module
{

    // asset tags
    const TAG_FRAGMENT = 'fragment';

    const TAG_CONTAINER = 'container';

    const TAG_EXTERNAL_DOCUMENT = 'external-document';

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

    const ATTR_ALIAS = 'as';

    const ATTR_PATH = 'path';

    const ATTR_TYPE = 'type';

    const ATTR_REALPATH = 'realpath';

    const ATTR_ASSETPATH = 'assetpath';

    const ATTR_REFERENCE = 'ref';

    const ATTR_USE = 'use';

    const ATTR_USE_DOCUMENT = 'document';

    const ATTR_USE_TEMPLATE = 'template';

    const ATTR_PARAM_KEY = 'name';

    const ATTR_PARAM_VAL = 'value';

    const EVENT_USE_DOCUMENT = 'use-document';

    const EVENT_USE_TEMPLATE = 'use-template';

    const EVENT_USE_SCRIPT = 'use-script';

    const EVENT_USE_STYLESHEET = 'use-stylesheet';

    const EVENT_SET_PARAMETER = 'set-parameter';

    const TEMPLATE_ERROR = 'slothsoft@farah/xsl/error';

    private $authority;

    private $manifest;

    private $rootAsset;

    private $assetDirectory;

    private $assetList = [];

    // TODO: AssetCache
    public function __construct(FarahUrlAuthority $authority, ManifestInterface $manifest, string $assetDirectory)
    {
        $this->authority = $authority;
        $this->manifest = $manifest;
        $this->assetDirectory = $assetDirectory;
    }

    public function getAuthority(): FarahUrlAuthority
    {
        return $this->authority;
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

    private function getRootAsset(): AssetInterface
    {
        if ($this->rootAsset === null) {
            $this->rootAsset = $this->loadRootAsset();
        }
        return $this->rootAsset;
    }

    private function loadRootAsset(): AssetInterface
    {
        $manifestElemet = $this->manifest->getRootElement();
        $manifestElemet->setAttribute('realpath', $this->assetDirectory);
        $manifestElemet->setAttribute('name', '');
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


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Node\ModuleNodeCreator;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Asset\ContainerAsset;
use RuntimeException;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Module implements EventTargetInterface
{
    use EventTargetTrait;

    // root tags
    const TAG_MODULE_ROOT = 'module';

    const TAG_CONFIGURATION_ROOT = 'default-configuration';

    // asset tags
    const TAG_FRAGMENT = 'fragment';

    const TAG_CONTAINER = 'container';

    const TAG_CONTROLLER = 'controller';

    // runtime-only asset tags
    const TAG_DOCUMENT = 'document';

    const TAG_ERROR = 'error';

    const TAG_CLOSURE = 'closure';

    // physical asset tags
    const TAG_ASSET_ROOT = 'assets';

    const TAG_RESOURCE = 'resource';

    const TAG_DIRECTORY = 'directory';

    const TAG_RESOURCE_DIRECTORY = 'resource-directory';

    // meta tags
    const TAG_SOURCE = 'source';

    const TAG_OPTIONS = 'options';

    const TAG_PARAM = 'param';

    // instruction tags
    const TAG_IMPORT = 'import';

    const TAG_USE_DOCUMENT = 'use-document';

    const TAG_USE_STYLESHEET = 'use-stylesheet';

    const TAG_USE_SCRIPT = 'use-script';

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

    const ATTR_PARAM_KEY = 'name';

    const ATTR_PARAM_VAL = 'value';

    const EVENT_USE_DOCUMENT = 'use-document';

    const EVENT_USE_TEMPLATE = 'use-template';

    const EVENT_USE_SCRIPT = 'use-script';

    const EVENT_USE_STYLESHEET = 'use-stylesheet';

    const EVENT_SET_PARAMETER = 'set-parameter';

    const TEMPLATE_ERROR = 'slothsoft@farah/xsl/error';

    private $authority;

    private $assetList;

    private $rootDirectory;

    private $manifestFile;

    /**
     */
    public function __construct(FarahUrlAuthority $authority)
    {
        $this->authority = $authority;
        
        $this->assetList = [];
        
        // TODO: create a const or something for the SERVER_ROOT . 'vendor' part
        $this->rootDirectory = SERVER_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $this->getVendor() . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR;
        $this->manifestFile = $this->rootDirectory . 'module.xml';
    }

    public function getAuthority(): FarahUrlAuthority
    {
        return $this->authority;
    }

    public function createUrl(FarahUrlPath $path, FarahUrlArguments $args): FarahUrl
    {
        return FarahUrl::createFromComponents($this->authority, $path, $args);
    }

    public function getManifestFile(): string
    {
        return $this->manifestFile;
    }

    public function manifestFileExists()
    {
        return is_file($this->manifestFile);
    }

    public function loadManifestFile()
    {
        if (! $this->manifestFileExists()) {
            throw new RuntimeException("Module {$this->getId()} is missing its manifest at $this->manifestFile.");
        }
        
        $dom = new DOMHelper();
        $moduleElement = LeanElement::createTreeFromDOMDocument($dom->loadDocument($this->manifestFile));
        
        $this->loadDefaultConfiguration($moduleElement->getChildByTag(self::TAG_CONFIGURATION_ROOT));
        $this->loadAssets($moduleElement->getChildByTag(self::TAG_ASSET_ROOT));
    }

    private function loadDefaultConfiguration(LeanElement $element)
    {}

    private function loadAssets(LeanElement $element)
    {
        $element->setAttribute('name', 'root');
        $element->setAttribute('realpath', $this->getRootDirectory() . 'assets');
        $element->setAttribute('assetpath', '');
        try {
            $this->assets = $this->createModuleNode($element);
        } catch (Throwable $e) {
            $this->assets = new ContainerAsset();
            throw $e;
        }
    }

    /**
     *
     * @return string
     */
    public function getVendor(): string
    {
        return $this->authority->getVendor();
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->authority->getModule();
    }

    public function getId(): string
    {
        return (string) $this->authority;
    }

    /**
     *
     * @return string
     */
    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    public function lookupAssetByPath(FarahUrlPath $path): AssetInterface
    {
        $id = (string) $path;
        if (! isset($this->assetList[$id])) {
            $this->assetList[$id] = $this->assets->traverseTo($id);
        }
        return $this->assetList[$id];
    }

    public function createModuleNode(LeanElement $element, LeanElement $parent = null)
    {
        return ModuleNodeCreator::getInstance()->create($this, $element, $parent);
    }
}


<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use DOMDocument;
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

    public static function createErrorDocument(Throwable $exception, AssetInterface $contextAsset = null): DOMDocument
    {
        $ret = new DOMDocument();
        $element = $ret->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_ERROR);
        $element->setAttribute('name', get_class($exception));
        $element->setAttribute('code', $exception->getCode());
        $element->setAttribute('file', $exception->getFile());
        $element->setAttribute('line', $exception->getLine());
        $element->setAttribute('message', $exception->getMessage());
        
        if ($contextAsset) {
            $element->setAttribute('asset', $contextAsset->getId());
        }
        
        $ret->appendChild($ret->createProcessingInstruction('xml-stylesheet', sprintf('type="text/xsl" href="/getAsset.php/%s"', self::TEMPLATE_ERROR)));
        $ret->appendChild($element);
        
        return $ret;
    }

    const TAG_MODULE = 'module';

    const TAG_CONFIGURATION_ROOT = 'default-configuration';

    const TAG_ASSET_ROOT = 'assets';

    const TAG_FRAGMENT = 'fragment';

    const TAG_DIRECTORY = 'directory';

    const TAG_RESOURCE = 'resource';

    const TAG_RESOURCE_DIRECTORY = 'resource-directory';

    const TAG_ARCHIVE = 'archive';

    const TAG_ARCHIVE_DIRECTORY = 'archive-directory';

    const TAG_INCLUDE_FRAGMENT = 'include-fragment';

    const TAG_PARAM = 'param';

    const TAG_CLOSURE = 'closure';

    const TAG_DOCUMENT = 'document';

    const TAG_ERROR = 'error';

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

    private $vendor;

    private $name;

    private $definitionFactory;

    private $rootDirectory;

    private $manifestFile;

    /**
     */
    public function __construct(string $vendor, string $name)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        
        $this->definitionFactory = new DefinitionFactory($this);
        
        // TODO: create a const or something for the SERVER_ROOT . 'vendor' part
        $this->rootDirectory = SERVER_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
        $this->manifestFile = $this->rootDirectory . 'module.xml';
    }

    public function getDefinitionFactory()
    {
        return $this->definitionFactory;
    }

    public function getManifestFile()
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
        $element->setAttribute('realpath', $this->getRootDirectory() . 'assets');
        $element->setAttribute('assetpath', '');
        $this->assets = $this->definitionFactory->createDefinition($element);
    }

    /**
     *
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return "farah://$this->vendor@$this->name";
    }

    /**
     *
     * @return string
     */
    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    public function getAssetDefinition(string $assetpath)
    {
        return $this->assets->traverseTo($assetpath);
    }

    public function getAsset(string $assetpath, array $args = [])
    {
        return AssetRepository::getInstance()->lookupAssetByUrl($this->assets->traverseTo($assetpath)
            ->toUrl($args));
    }
}


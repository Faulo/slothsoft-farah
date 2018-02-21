<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use DOMDocument;
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

    const EVENT_USE_DOCUMENT = 'use-document';

    const EVENT_USE_TEMPLATE = 'use-template';

    const EVENT_USE_SCRIPT = 'use-script';

    const EVENT_USE_STYLESHEET = 'use-stylesheet';

    const EVENT_SET_PARAMETER = 'set-parameter';

    const TEMPLATE_ERROR = 'slothsoft@farah/xsl/error';

    private $vendor;

    private $name;

    private $rootDirectory;

    /**
     */
    public function __construct(string $vendor, string $name)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        
        // TODO: create a const or something for the SERVER_ROOT . 'vendor' part
        $this->rootDirectory = SERVER_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function init()
    {
        $manifestFile = $this->getRootDirectory() . 'module.xml';
        assert(is_file($manifestFile), "Module {$this->getId()} is missing its manifest at $manifestFile.");
        
        $dom = new DOMHelper();
        $moduleDoc = $dom->loadDocument($manifestFile);
        $moduleXPath = $dom->loadXPath($moduleDoc, DOMHelper::XPATH_SLOTHSOFT);
        
        $element = $moduleXPath->evaluate('/sfm:module/sfm:assets')->item(0);
        
        assert($element, "Module {$this->getId()}'s manifest does not contain <sfm:assets>.");
        
        $element->setAttribute('realpath', $this->getRootDirectory() . 'assets');
        $element->setAttribute('assetpath', '');
        $this->assets = DefinitionFactory::createFromElement($this, $element);
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
        return AssetRepository::getInstance()->lookupAssetByUrl($this->assets->traverseTo($assetpath)->toUrl($args));
    }
}


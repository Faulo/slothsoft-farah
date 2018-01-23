<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use Slothsoft\Farah\Module\Resources\PhpResource;
use DOMElement;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Module
{
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
    const TAG_DATA = 'data';
    
    const TAG_USE_DATA = 'use-data';
    const TAG_USE_STYLESHEET = 'use-stylesheet';
    const TAG_USE_SCRIPT = 'use-script';
    const TAG_USE_TEMPLATE = 'use-template';
    
    
    
    private $vendor;
    private $name;
    private $rootDirectory;

    /**
     */
    public function __construct(string $vendor, string $name)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        
        //TODO: create a const or something for the SERVER_ROOT . 'vendor' part
        $this->rootDirectory = SERVER_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
        
        $dom = new DOMHelper();
        $moduleDoc = $dom->loadDocument($this->getRootDirectory() . 'module.xml');
        $moduleXPath = $dom->loadXPath($moduleDoc, DOMHelper::XPATH_SLOTHSOFT);
        
        if ($element = $moduleXPath->evaluate('/sfm:module/sfm:assets')->item(0)) {
            $element->setAttribute('realpath', $this->getRootDirectory() . 'assets');
            $element->setAttribute('assetpath', '');
            
            $this->assets = AssetDefinition::createFromElement($this, $element);
        } else {
            throw new UnexpectedValueException("module {$this->getId()} does not contain <assets>");
        }
    }
    
    
    
    
    /**
     * @return string
     */
    public function getVendor() : string
    {
        return $this->vendor;
    }
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getId() : string
    {
        return "farah://$this->vendor@$this->name";
    }
    
    /**
     * @return string
     */
    public function getRootDirectory() : string
    {
        return $this->rootDirectory;
    }
    
    public function getAssetDefinition(string $assetpath) {
        return $this->assets->traverseTo($assetpath);
    }
}


<?php
namespace Slothsoft\Farah\Module\Element;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Assets\AssetFactory;
use Slothsoft\Farah\Module\Element\Instruction\InstructionFactory;
use Slothsoft\Farah\Module\Element\Meta\MetaFactory;
use DomainException;
use Slothsoft\Farah\Module\Assets\Resources\ResourceFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleElementCreator //TOOD: find a better name maybe
{
    public static function getInstance() : ModuleElementCreator
    {
        static $instance;
        if ($instance === null) {
            $instance = new ModuleElementCreator();
        }
        return $instance;
    }
    
    private $factoryMap = [];
    public function __construct() {
        $assetFactory = new AssetFactory();
        $resourceFactory = new ResourceFactory();
        $instructionFactory = new InstructionFactory();
        $metaFactory = new MetaFactory();
        
        $this->factoryMap[Module::TAG_MODULE_ROOT] = $assetFactory;
        $this->factoryMap[Module::TAG_ASSET_ROOT] = $assetFactory;
        $this->factoryMap[Module::TAG_DIRECTORY] = $assetFactory;
        $this->factoryMap[Module::TAG_FRAGMENT] = $assetFactory;
        $this->factoryMap[Module::TAG_RESOURCE_DIRECTORY] = $assetFactory;
        $this->factoryMap[Module::TAG_CALL_CONTROLLER] = $assetFactory;
        
        $this->factoryMap[Module::TAG_RESOURCE] = $resourceFactory;
        
        $this->factoryMap[Module::TAG_INCLUDE_FRAGMENT] = $instructionFactory;
        $this->factoryMap[Module::TAG_USE_DOCUMENT] = $instructionFactory;
        $this->factoryMap[Module::TAG_USE_TEMPLATE] = $instructionFactory;
        $this->factoryMap[Module::TAG_USE_STYLESHEET] = $instructionFactory;
        $this->factoryMap[Module::TAG_USE_SCRIPT] = $instructionFactory;
        
        $this->factoryMap[Module::TAG_PARAM] = $metaFactory;
        $this->factoryMap[Module::TAG_SOURCE] = $metaFactory;
        $this->factoryMap[Module::TAG_OPTIONS] = $metaFactory;
    }
    public function create(Module $ownerModule, LeanElement $element, LeanElement $parent = null) : ModuleElement {
        $tag = $element->getTag();
        if (!isset($this->factoryMap[$tag])) {
            throw new DomainException("Module tag <$tag> is not supported by this implementation.");
        }
        return $this->factoryMap[$tag]->create(
            $this,
            $ownerModule, 
            $element, 
            $parent
        );
    }
    
    public function createList(Module $ownerModule, array $elementList, LeanElement $parent = null) : array
    {
        $ret = [];
        foreach ($elementList as $element) {
            $ret[] = $this->create($ownerModule, $element, $parent);
        }
        return $ret;
    }
}


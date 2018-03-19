<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\TagNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetFactory;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetFactory;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource\ResourceFactory;
use Slothsoft\Farah\Module\Node\Meta\MetaFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleNodeCreator // TOOD: find a better name maybe
{

    public static function getInstance(): ModuleNodeCreator
    {
        static $instance;
        if ($instance === null) {
            $instance = new ModuleNodeCreator();
        }
        return $instance;
    }

    private $factoryMap = [];

    public function __construct()
    {
        $assetFactory = new AssetFactory();
        $physicalFactory = new PhysicalAssetFactory();
        $resourceFactory = new ResourceFactory();
        $metaFactory = new MetaFactory();
        
        $this->factoryMap[Module::TAG_CLOSURE] = $assetFactory;
        $this->factoryMap[Module::TAG_CONTAINER] = $assetFactory;
        $this->factoryMap[Module::TAG_FRAGMENT] = $assetFactory;
        $this->factoryMap[Module::TAG_EXTERNAL_DOCUMENT] = $assetFactory;
        $this->factoryMap[Module::TAG_CUSTOM_ASSET] = $assetFactory;
        
        $this->factoryMap[Module::TAG_DIRECTORY] = $physicalFactory;
        $this->factoryMap[Module::TAG_RESOURCE_DIRECTORY] = $physicalFactory;
        $this->factoryMap[Module::TAG_ASSET_ROOT] = $physicalFactory;
        
        $this->factoryMap[Module::TAG_RESOURCE] = $resourceFactory;
        
        $this->factoryMap[Module::TAG_IMPORT] = $metaFactory;
        $this->factoryMap[Module::TAG_PARAM] = $metaFactory;
        $this->factoryMap[Module::TAG_USE_DOCUMENT] = $metaFactory;
        $this->factoryMap[Module::TAG_USE_TEMPLATE] = $metaFactory;
        $this->factoryMap[Module::TAG_LINK_SCRIPT] = $metaFactory;
        $this->factoryMap[Module::TAG_LINK_STYLESHEET] = $metaFactory;
        $this->factoryMap[Module::TAG_SOURCE] = $metaFactory;
        $this->factoryMap[Module::TAG_OPTIONS] = $metaFactory;
    }

    public function create(Module $ownerModule, LeanElement $element, LeanElement $parent = null): ModuleNodeInterface
    {
        $tag = $element->getTag();
        if (! isset($this->factoryMap[$tag])) {
            throw new TagNotSupportedException(DOMHelper::NS_FARAH_MODULE, $tag);
        }
        return $this->factoryMap[$tag]->create($this, $ownerModule, $element, $parent);
    }
}


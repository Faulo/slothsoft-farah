<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\TagNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetFactory;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetFactory;
use Slothsoft\Farah\Module\Node\Meta\MetaFactory;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleNodeCreator // TOOD: find a better name maybe
{

    public static function getInstance(): self
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private $factoryMap = [];

    public function __construct()
    {
        $assetFactory = new AssetFactory();
        $fileFactory = new PhysicalAssetFactory();
        $metaFactory = new MetaFactory();
        
        // assets
        $this->factoryMap[Module::TAG_CONTAINER] = $assetFactory;
        $this->factoryMap[Module::TAG_FRAGMENT] = $assetFactory;
        $this->factoryMap[Module::TAG_CUSTOM_ASSET] = $assetFactory;
        $this->factoryMap[Module::TAG_EXTERNAL_RESOURCE] = $assetFactory;
        $this->factoryMap[Module::TAG_CLOSURE] = $assetFactory;
        
        // files
        $this->factoryMap[Module::TAG_RESOURCE] = $fileFactory;
        $this->factoryMap[Module::TAG_DIRECTORY] = $fileFactory;
        $this->factoryMap[Module::TAG_RESOURCE_DIRECTORY] = $fileFactory;
        $this->factoryMap[Module::TAG_ASSET_ROOT] = $fileFactory;
        
        // meta
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


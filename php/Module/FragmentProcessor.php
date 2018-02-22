<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Event\Events\SetParameterEvent;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\Assets\Fragment;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FragmentProcessor implements EventTargetInterface
{
    use EventTargetTrait;

    private $context;

    public function __construct(Fragment $context)
    {
        $this->context = $context;
    }

    public function process()
    {
        $definition = $this->context->getDefinition();
        foreach ($definition->getChildren() as $child) {
            $this->loadChildDefinition($child, $this->context);
        }
    }

    private function loadChildDefinition(AssetDefinitionInterface $assetDefinition, AssetInterface $contextAsset)
    {
        $assetTag = $assetDefinition->getElementTag();
        $event = null;
        switch ($assetTag) {
            case Module::TAG_INCLUDE_FRAGMENT:
                $asset = $contextAsset->lookupAsset($assetDefinition->getElementAttribute(Module::ATTR_REFERENCE));
                $element = $asset->getDefinition();
                foreach ($element->getChildren() as $childNode) {
                    $this->loadChildDefinition($childNode, $asset);
                }
                break;
            case Module::TAG_USE_DOCUMENT:
                foreach ($assetDefinition->getChildren() as $childNode) {
                    $this->loadChildDefinition($childNode, $this->context);
                }
                $asset = $contextAsset->lookupAsset($assetDefinition->getElementAttribute(Module::ATTR_REFERENCE), $this->context->getArguments());
                assert($asset instanceof DOMWriterInterface, "To <sfm:use-document> asset {$asset->getId()}, it must be a DOMWriterInterface.");
                
                $event = new UseAssetEvent();
                $event->initEvent(Module::EVENT_USE_DOCUMENT, [
                    'definition' => $assetDefinition,
                    'asset' => $asset
                ]);
                break;
            case Module::TAG_FRAGMENT:
                $ref = $contextAsset->getAssetPath() . '/' . $assetDefinition->getName();
                $asset = $contextAsset->lookupAsset($ref, $this->context->getArguments());
                assert($asset instanceof DOMWriterInterface, "Asset reference $ref must be unique.");
                
                $event = new UseAssetEvent();
                $event->initEvent(Module::EVENT_USE_DOCUMENT, [
                    'definition' => $assetDefinition,
                    'asset' => $asset
                ]);
                break;
            case Module::TAG_USE_TEMPLATE:
                $asset = $contextAsset->lookupAsset($assetDefinition->getElementAttribute(Module::ATTR_REFERENCE), $this->context->getArguments());
                assert($asset instanceof FileWriterInterface, "To <sfm:use-template> asset {$asset->getId()}, it must be a FileWriterInterface.");
                
                $event = new UseAssetEvent();
                $event->initEvent(Module::EVENT_USE_TEMPLATE, [
                    'definition' => $assetDefinition,
                    'asset' => $asset
                ]);
                break;
            case Module::TAG_USE_STYLESHEET:
                $asset = $contextAsset->lookupAsset($assetDefinition->getElementAttribute(Module::ATTR_REFERENCE), $this->context->getArguments());
                assert($asset instanceof FileWriterInterface, "To <sfm:use-stylesheet> asset {$asset->getId()}, it must be a FileWriterInterface.");
                
                $event = new UseAssetEvent();
                $event->initEvent(Module::EVENT_USE_STYLESHEET, [
                    'definition' => $assetDefinition,
                    'asset' => $asset
                ]);
                break;
            case Module::TAG_USE_SCRIPT:
                $asset = $contextAsset->lookupAsset($assetDefinition->getElementAttribute(Module::ATTR_REFERENCE), $this->context->getArguments());
                assert($asset instanceof FileWriterInterface, "To <sfm:use-script> asset {$asset->getId()}, it must be a FileWriterInterface.");
                
                $event = new UseAssetEvent();
                $event->initEvent(Module::EVENT_USE_SCRIPT, [
                    'definition' => $assetDefinition,
                    'asset' => $asset
                ]);
                break;
            case Module::TAG_PARAM:
                // TODO: <param>
                $event = new SetParameterEvent();
                $event->initEvent(Module::EVENT_SET_PARAMETER, [
                    'name' => $assetDefinition->getElementAttribute(Module::ATTR_PARAM_KEY),
                    'value' => $assetDefinition->getElementAttribute(Module::ATTR_PARAM_VAL)
                ]);
                break;
            default:
                throw ExceptionContext::append(new DomainException("Fragment tag <sfm:$assetTag> is not supported by this implementation."), [
                    'definition' => $assetDefinition,
                    'asset' => $this->context
                ]);
        }
        
        if ($event) {
            $this->dispatchEvent($event);
        }
    }
}


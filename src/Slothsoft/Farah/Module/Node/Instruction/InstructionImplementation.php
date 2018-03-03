<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Node\ModuleNodeImplementation;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class InstructionImplementation extends ModuleNodeImplementation implements InstructionInterface
{

    public function getReference(): string
    {
        return $this->getElementAttribute(Module::ATTR_REFERENCE);
    }

    public function getAlias(): string
    {
        return $this->getElementAttribute(Module::ATTR_ALIAS, $this->getReferencedAsset()
            ->getName());
    }

    public function getReferencedAsset(): AssetInterface
    {
        $url = FarahUrl::createFromReference($this->getReference(), $this->getOwnerModule()->getAuthority());
        return FarahUrlResolver::resolveToAsset($url);
    }

    public function createUseAssetEvent(string $type): UseAssetEvent
    {
        $event = new UseAssetEvent();
        $event->initEvent($type, [
            'asset' => $this->getReferencedAsset(),
            'assetArguments' => $this->getManifestArguments(),
            'assetName' => $this->getAlias()
        ]);
        return $event;
    }
}


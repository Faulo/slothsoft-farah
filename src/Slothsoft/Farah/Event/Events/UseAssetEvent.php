<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Event\Events;

use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseAssetEvent extends GenericEvent
{

    private $asset;

    private $assetArguments;

    private $assetName;

    public function initEvent(string $type, array $options)
    {
        parent::initEvent($type, $options);
        
        $this->asset = $options['asset'] ?? null;
        assert($this->asset instanceof AssetInterface, "UseAssetEvent requires an 'asset' option that implements AssetInterface.");
        
        $this->assetArguments = $options['assetArguments'] ?? $this->asset->getManifestArguments();
        $this->assetName = $options['assetName'] ?? $this->asset->getName();
    }

    public function getAsset(): AssetInterface
    {
        return $this->asset;
    }

    public function getAssetArguments(): FarahUrlArguments
    {
        return $this->assetArguments;
    }

    public function getAssetName(): string
    {
        return $this->assetName;
    }
}


<?php declare(strict_types=1);
namespace Slothsoft\Farah\Event\Events;

use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\Assets\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseAssetEvent extends GenericEvent
{

    private $definition;

    private $asset;

    public function initEvent(string $type, array $options)
    {
        parent::initEvent($type, $options);
        
        $this->definition = $options['definition'] ?? null;
        $this->asset = $options['asset'] ?? null;
    }

    public function getDefinition(): AssetDefinitionInterface
    {
        return $this->definition;
    }

    public function getAsset(): AssetInterface
    {
        return $this->asset;
    }
}


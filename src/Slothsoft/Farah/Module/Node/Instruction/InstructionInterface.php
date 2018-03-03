<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface InstructionInterface extends ModuleNodeInterface
{

    public function getReference(): string;

    public function getAlias(): string;

    public function getReferencedAsset(): AssetInterface;

    public function createUseAssetEvent(string $type): UseAssetEvent;
}


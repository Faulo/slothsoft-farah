<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element\Instruction;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\Element\ModuleElementInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface InstructionInterface extends ModuleElementInterface
{
    public function initInstruction(Module $ownerModule, LeanElement $element, array $children);
    public function getReference() : string;
    public function getAlias(): string;
    public function getReferencedAsset() : AssetInterface;
    public function createUseAssetEvent(string $type) : UseAssetEvent;
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface PhysicalAssetInterface extends AssetInterface, FileWriterInterface
{

    public function getPath(): string;

    public function getRealPath(): string;
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ResourceInterface extends AssetInterface, FileWriterInterface
{
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface PhysicalAssetInterface extends AssetInterface
{
    /**
     * "path" attribute
     * 
     * @return string
     */
    public function getPath(): string;

    /**
     * "realpath" attribute
     * 
     * @return string
     */
    public function getRealPath(): string;
    
    /**
     * @todo should this return what is, or what ought to be?
     * 
     * @return bool
     */
    public function isDirectory() : bool;
    
    /**
     * @todo should this return what is, or what ought to be?
     *
     * @return bool
     */
    public function isFile() : bool;
}


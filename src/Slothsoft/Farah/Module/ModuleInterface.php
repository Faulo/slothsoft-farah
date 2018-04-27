<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

interface ModuleInterface
{
    /**
     * The Farah URL that represents this module.
     * 
     * @return string
     */
    public function getId() : string;
    
    /**
     * @param FarahUrlPath|string $path
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public function createUrl($path = null, $args = null, $fragment = null): FarahUrl;
    
    /**
     * @param FarahUrlPath|string $path
     * @return AssetInterface
     */
    public function lookupAsset($path): AssetInterface;
}


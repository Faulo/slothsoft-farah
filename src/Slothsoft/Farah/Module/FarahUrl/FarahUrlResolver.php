<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\ModuleRepository;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlResolver
{
    public static function resolveToModule(FarahUrl $url): Module
    {
        return ModuleRepository::getInstance()->lookupModuleByAuthority($url->getAuthority());
    }
    public static function resolveToAsset(FarahUrl $url): AssetInterface
    {
        return self::resolveToModule($url)->lookupAssetByPath($url->getPath());
    }
    public static function resolveToResult(FarahUrl $url): ResultInterface
    {
        return self::resolveToAsset($url)->lookupResultByArguments($url->getArguments());
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlResolver
{

    public static function resolveToModule(FarahUrl $url): Module
    {
        return Kernel::getInstance()->lookupModule($url->getAssetAuthority());
    }

    public static function resolveToAsset(FarahUrl $url): AssetInterface
    {
        return self::resolveToModule($url)->lookupAsset($url->getAssetPath());
    }

    public static function resolveToExecutable(FarahUrl $url): ExecutableInterface
    {
        return self::resolveToAsset($url)->lookupExecutable($url->getArguments());
    }

    public static function resolveToResult(FarahUrl $url): ResultInterface
    {
        return self::resolveToExecutable($url)->lookupResult($url->getStreamIdentifier());
    }

    public static function resolveToStream(FarahUrl $url): StreamInterface
    {
        return self::resolveToResult($url)->lookupStream();
    }

    public static function resolveToDocument(FarahUrl $url): DOMDocument
    {
        return self::resolveToExecutable($url)->lookupXmlResult()->toDocument();
    }
}


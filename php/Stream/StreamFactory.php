<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Stream;

use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Stream\Streams\FileStream;
use Slothsoft\Farah\Stream\Streams\StringStream;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class StreamFactory
{

    public static function createStream(string $path, string $mode, int $options)
    {
        $asset = AssetRepository::getInstance()->lookupAssetByUrl(FarahUrl::createFromUri($path));
        
        switch (true) {
            case $asset instanceof FileWriterInterface:
                return new FileStream($asset->toFile()->getPath(), $mode);
            case $asset instanceof DOMWriterInterface:
                return new StringStream($asset->toDocument()->saveXML());
        }
        throw new RuntimeException("asset {$asset->getId()} does not implement a streamable interface!");
    }
}


<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Stream;

use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Stream\Streams\FileStream;
use Slothsoft\Farah\Stream\Streams\StringStream;
use RuntimeException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;

/**
 *
 * @author Daniel Schulz
 *        
 */
class StreamFactory
{

    public static function createStream(string $path, string $mode, int $options)
    {
        $result = FarahUrlResolver::resolveToResult(FarahUrl::createFromReference($path));
        
        switch (true) {
            case $result instanceof FileWriterInterface:
                return new FileStream($result->toFile()->getPath(), $mode);
            case $result instanceof DOMWriterInterface:
                return new StringStream($result->toDocument()->saveXML());
        }
        throw new RuntimeException("asset {$result->getId()} does not implement a streamable interface!");
    }
}


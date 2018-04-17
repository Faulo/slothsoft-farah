<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Stream;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Stream\Streams\FileStream;

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
        
        return new FileStream($result->toFile()->getPath(), $mode);
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Core\StreamWrapper\Psr7StreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahStreamWrapperFactory implements StreamWrapperFactoryInterface
{

    public function createStreamWrapper(string $url, string $mode, int $options)
    {
        $url = FarahUrl::createFromReference($url);
        
        $stream = FarahUrlResolver::resolveToStream($url);
        
        return new Psr7StreamWrapper($stream);
    }

    public function statUrl(string $url, int $flags)
    {
        $url = FarahUrl::createFromReference($url);
        
        $stream = FarahUrlResolver::resolveToStream($url);
        
        return $stream->getMetadata();
    }
}


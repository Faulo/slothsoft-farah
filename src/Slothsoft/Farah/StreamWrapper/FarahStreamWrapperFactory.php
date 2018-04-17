<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Farah\Exception\StreamTypeNotSupportedException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;

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
        
        $result = FarahUrlResolver::resolveToResult($url);
        
        switch ((string) $url->getStreamIdentifier()) {
            case ResultInterface::STREAM_TYPE_DEFAULT:
                return $result->createDefaultStreamWrapper();
            case ResultInterface::STREAM_TYPE_XML:
                return $result->createXmlStreamWrapper();
            default:
                throw new StreamTypeNotSupportedException((string) $url);
        }
    }

    public function statUrl(string $url, int $flags)
    {
        return [];
    }
}


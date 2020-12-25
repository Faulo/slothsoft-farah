<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Core\StreamWrapper\Psr7StreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahStreamWrapperFactory implements StreamWrapperFactoryInterface {

    public function createStreamWrapper(string $url, string $mode, int $options) {
        my_dump($url);
        $url = FarahUrl::createFromReference($url);

        $stream = Module::resolveToStreamWriter($url)->toStream();

        return new Psr7StreamWrapper($stream);
    }

    public function statUrl(string $url, int $flags) {
        my_dump($url);
        $url = FarahUrl::createFromReference($url);

        $result = Module::resolveToResult($url);

        return $result->lookupFileStatistics();
    }
}


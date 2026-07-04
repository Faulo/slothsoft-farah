<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Core\StreamWrapper\Psr7StreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * Stream wrapper factory for resolving farah:// URLs into readable streams.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class FarahStreamWrapperFactory implements StreamWrapperFactoryInterface {
    
    public function createStreamWrapper(string $url, string $mode, int $options): StreamWrapperInterface {
        $url = FarahUrl::createFromReference($url);
        
        $stream = Module::resolveToStreamWriter($url)->toStream();
        
        return new Psr7StreamWrapper($stream);
    }
    
    public function statUrl(string $url, int $flags): array|bool {
        try {
            $url = FarahUrl::createFromReference($url);
        } catch (IncompleteUrlException) {
            return false;
        }
        
        try {
            return Module::resolveToResult($url)->lookupFileStatistics();
        } catch (HttpDownloadException) {
            return true;
        } catch (HttpStatusException $e) {
            return $e->getCode() < 400;
        } catch (EmptyTransformationException|AssetPathNotFoundException|ModuleNotFoundException) {
            return false;
        }
    }
}


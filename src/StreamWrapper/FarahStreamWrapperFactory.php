<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Core\StreamWrapper\Psr7StreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahStreamWrapperFactory implements StreamWrapperFactoryInterface {

    public function createStreamWrapper(string $url, string $mode, int $options) {
        $url = FarahUrl::createFromReference($url);

        $stream = Module::resolveToStreamWriter($url)->toStream();

        return new Psr7StreamWrapper($stream);
    }

    public function statUrl(string $url, int $flags) {
        try {
            $url = FarahUrl::createFromReference($url);
        } catch (IncompleteUrlException $e) {
            return false;
        }

        try {
            return Module::resolveToResult($url)->lookupFileStatistics();
        } catch (EmptyTransformationException $e) {
            return false;
        } catch (AssetPathNotFoundException $e) {
            return false;
        } catch (ModuleNotFoundException $e) {
            return false;
        }
    }
}


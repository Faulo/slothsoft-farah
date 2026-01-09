<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Ds\Map;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

/**
 *
 * @see https://www.iana.org/assignments/http-parameters/http-parameters.xhtml#content-coding
 */
final class ContentCoding {
    
    private const NO_ENCODING = 'identity';
    
    public static function identity(): ConfigurableCoding {
        static $instance;
        if ($instance === null) {
            $instance = new ConfigurableCoding();
            $instance->setEncodingFilter(IdentityFactory::getInstance());
            $instance->setDecodingFilter(IdentityFactory::getInstance());
        }
        return $instance;
    }
    
    private static function initStatic(): Map {
        static $codings;
        if ($codings === null) {
            $codings = new Map();
            $codings->put(self::NO_ENCODING, self::identity());
        }
        return $codings;
    }
    
    public static function getCoding(string $name): ConfigurableCoding {
        $codings = self::initStatic();
        if ($codings->hasKey($name)) {
            return $codings->get($name);
        }
        
        $coding = new ConfigurableCoding($name);
        $codings->put($name, $coding);
        return $coding;
    }
    
    public static function registerEncodingFilter(string $name, FilteredStreamWriterInterface $writer) {
        self::getCoding($name)->setEncodingFilter($writer);
    }
    
    public static function unregisterEncodingFilter(string $name) {
        self::getCoding($name)->clearEncodingFilter();
    }
    
    public static function getEncodings(): iterable {
        /** @var ConfigurableCoding $coding **/
        foreach (self::initStatic() as $name => $coding) {
            if ($coding->hasEncodingFilter()) {
                yield $name => $coding;
            }
        }
    }
    
    public static function registerDecodingFilter(string $name, FilteredStreamWriterInterface $writer) {
        self::getCoding($name)->setDecodingFilter($writer);
    }
    
    public static function unregisterDecodingFilter(string $name) {
        self::getCoding($name)->clearDecodingFilter();
    }
    
    public static function getDecodings(): iterable {
        /** @var ConfigurableCoding $coding **/
        foreach (self::initStatic() as $name => $coding) {
            if ($coding->hasDecodingFilter()) {
                yield $name => $coding;
            }
        }
    }
}


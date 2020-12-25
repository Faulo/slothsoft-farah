<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

/**
 *
 * @see https://www.iana.org/assignments/http-parameters/http-parameters.xhtml#transfer-coding
 */
class TransferCoding implements CodingInterface {

    const NO_ENCODING = 'identity';

    private static $codings;

    private static function initStatic() {
        if (static::$codings === null) {
            static::$codings = [];
            static::$codings[static::NO_ENCODING] = new self(static::NO_ENCODING);
            static::$codings[static::NO_ENCODING]->setEncodingFilter(IdentityFactory::getInstance());
            static::$codings[static::NO_ENCODING]->setDecodingFilter(IdentityFactory::getInstance());
        }
    }

    public static function getCoding(string $name): self {
        static::initStatic();
        if (! isset(static::$codings[$name])) {
            static::$codings[$name] = new static($name);
        }
        return static::$codings[$name];
    }

    public static function identity(): self {
        return static::getCoding(static::NO_ENCODING);
    }

    public static function registerEncodingFilter(string $name, FilteredStreamWriterInterface $writer) {
        static::getCoding($name)->setEncodingFilter($writer);
    }

    public static function unregisterEncodingFilter(string $name) {
        static::getCoding($name)->clearEncodingFilter();
    }

    public static function getEncodings(): array {
        static::initStatic();
        return array_filter(static::$codings, function (CodingInterface $coding) {
            return $coding->hasEncodingFilter();
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function registerDecodingFilter(string $name, FilteredStreamWriterInterface $writer) {
        static::getCoding($name)->setDecodingFilter($writer);
    }

    public static function unregisterDecodingFilter(string $name) {
        static::getCoding($name)->clearDecodingFilter();
    }

    public static function getDecodings(): array {
        static::initStatic();
        return array_filter(static::$codings, function (CodingInterface $coding) {
            return $coding->hasDecodingFilter();
        }, ARRAY_FILTER_USE_BOTH);
    }

    private $httpName;

    private $encodingWriter;

    private $decodingWriter;

    private function __construct(string $httpName) {
        $this->httpName = $httpName;
    }

    public function __toString(): string {
        return $this->httpName;
    }

    public function isNoEncoding(): bool {
        return $this->httpName === static::NO_ENCODING;
    }

    public function setEncodingFilter(FilteredStreamWriterInterface $encodingWriter) {
        $this->encodingWriter = $encodingWriter;
    }

    public function clearEncodingFilter() {
        $this->encodingWriter = null;
    }

    public function hasEncodingFilter(): bool {
        return $this->encodingWriter !== null;
    }

    public function encodeStream(StreamInterface $stream): StreamInterface {
        return $this->encodingWriter->toFilteredStream($stream);
    }

    public function setDecodingFilter(FilteredStreamWriterInterface $decodingWriter) {
        $this->decodingWriter = $decodingWriter;
    }

    public function clearDecodingFilter() {
        $this->decodingWriter = null;
    }

    public function hasDecodingFilter(): bool {
        return $this->decodingWriter !== null;
    }

    public function decodeStream(StreamInterface $stream): StreamInterface {
        return $this->decodingWriter->toFilteredStream($stream);
    }
}


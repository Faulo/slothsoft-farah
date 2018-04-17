<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

/**
 *
 * @see https://www.iana.org/assignments/http-parameters/http-parameters.xhtml#transfer-coding
 */
class TransferCoding
{

    const NO_ENCODING = 'identity';

    public static function values(): array
    {
        return [
            new self('chunked'),
            new self('compress'),
            new self('deflate'),
            new self('gzip')
        ];
    }

    public static function identity(): TransferCoding
    {
        return new self(self::NO_ENCODING);
    }

    private $httpName;

    private function __construct(string $httpName)
    {
        $this->httpName = $httpName;
    }

    public function __toString(): string
    {
        return $this->httpName;
    }

    public function getHttpName(): string
    {
        return $this->httpName;
    }

    public function getFilterName(): string
    {
        return "http.transfer-encoding.$this->httpName";
    }

    public function isAvailable(): bool
    {
        return in_array($this->getFilterName(), stream_get_filters());
    }

    public function isNoEncoding(): bool
    {
        return $this->httpName === self::NO_ENCODING;
    }
}


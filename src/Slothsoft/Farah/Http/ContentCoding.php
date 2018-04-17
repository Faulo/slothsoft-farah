<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

/**
 * @see https://www.iana.org/assignments/http-parameters/http-parameters.xhtml#content-coding
 */
class ContentCoding
{
    const NO_ENCODING = 'identity';
    
    public static function values() : array {
        return [
            new self('aes128gcm'),
            new self('br'),
            new self('compress'),
            new self('deflate'),
            new self('exi'),
            new self('gzip'),
            new self('pack200-gzip'),
        ];
    }
    private $httpName;
    private function __construct(string $httpName) {
        $this->httpName = $httpName;
    }
    public function __toString() : string {
        return $this->httpName;
    }
    public function getHttpName() : string {
        return $this->httpName;
    }
    public function getFilterName() : string {
        return "http.content-encoding.$this->httpName";
    }
    public function isAvailable() : bool {
        return in_array($this->getFilterName(), stream_get_filters());
    }
    public function isNoEncoding() : bool {
        return $this->httpName === self::NO_ENCODING;
    }
}


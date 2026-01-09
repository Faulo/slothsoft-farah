<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

final class ConfigurableCoding implements CodingInterface {
    
    private string $httpName;
    
    private ?FilteredStreamWriterInterface $encodingWriter = null;
    
    private ?FilteredStreamWriterInterface $decodingWriter = null;
    
    public function __construct(string $httpName = '') {
        $this->httpName = $httpName;
    }
    
    public function getHttpName(): string {
        return $this->httpName;
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


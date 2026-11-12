<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use LogicException;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

/**
 * HTTP coding implementation that wraps a configurable set of stream filters.
 *
 * @author Daniel Schulz
 * @since 2026-01-09
 */
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
    
    public function setEncodingFilter(FilteredStreamWriterInterface $encodingWriter): void {
        $this->encodingWriter = $encodingWriter;
    }
    
    public function clearEncodingFilter(): void {
        $this->encodingWriter = null;
    }
    
    public function hasEncodingFilter(): bool {
        return $this->encodingWriter !== null;
    }
    
    public function encodeStream(StreamInterface $stream): StreamInterface {
        $writer = $this->encodingWriter ?? throw new LogicException(sprintf('No encoding filter is configured for "%s".', $this->httpName));
        return $writer->toFilteredStream($stream);
    }
    
    public function setDecodingFilter(FilteredStreamWriterInterface $decodingWriter): void {
        $this->decodingWriter = $decodingWriter;
    }
    
    public function clearDecodingFilter(): void {
        $this->decodingWriter = null;
    }
    
    public function hasDecodingFilter(): bool {
        return $this->decodingWriter !== null;
    }
    
    public function decodeStream(StreamInterface $stream): StreamInterface {
        $writer = $this->decodingWriter ?? throw new LogicException(sprintf('No decoding filter is configured for "%s".', $this->httpName));
        return $writer->toFilteredStream($stream);
    }
}


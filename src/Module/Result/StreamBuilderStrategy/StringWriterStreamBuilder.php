<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Adapter\ChunkWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\DOMWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\FileWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromStringWriter;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class StringWriterStreamBuilder implements StreamBuilderStrategyInterface {
    
    private StringWriterInterface $writer;
    
    private string $documentName;
    
    private string $extension;
    
    private string $charset;
    
    public function __construct(StringWriterInterface $writer, string $documentName = 'data', string $extension = 'txt', string $charset = 'UTF-8') {
        $this->writer = $writer;
        $this->documentName = $documentName;
        $this->extension = $extension;
        $this->charset = $charset;
    }
    
    public function buildStreamMimeType(ResultInterface $context): string {
        return MimeTypeDictionary::guessMime($this->extension);
    }
    
    public function buildStreamCharset(ResultInterface $context): string {
        return $this->charset;
    }
    
    public function buildStreamFileName(ResultInterface $context): string {
        return $this->extension === '' ? $this->documentName : "$this->documentName.$this->extension";
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array {
        return [];
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return md5($this->writer->toString());
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return true;
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromStringWriter($this->writer);
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return new FileWriterFromStringWriter($this->writer);
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return new DOMWriterFromStringWriter($this->writer);
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return new ChunkWriterFromStringWriter($this->writer);
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->writer;
    }
}


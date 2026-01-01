<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Adapter\DOMWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\FileWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromChunkWriter;
use Slothsoft\Core\IO\Writable\Adapter\StringWriterFromChunkWriter;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Generator;

class ChunkWriterStreamBuilder implements StreamBuilderStrategyInterface, ChunkWriterInterface {
    
    private ChunkWriterInterface $writer;
    
    private string $fileName;
    
    private bool $isBufferable;
    
    public function __construct(ChunkWriterInterface $writer, string $fileName, bool $isBufferable = true) {
        $this->writer = $writer;
        $this->fileName = $fileName;
        $this->isBufferable = $isBufferable;
    }
    
    public function toChunks(): Generator {
        return $this->writer->toChunks();
    }
    
    public function buildStreamMimeType(ResultInterface $context): string {
        return MimeTypeDictionary::guessMime(pathinfo($this->fileName, PATHINFO_EXTENSION));
    }
    
    public function buildStreamCharset(ResultInterface $context): string {
        return 'UTF-8';
    }
    
    public function buildStreamFileName(ResultInterface $context): string {
        return $this->fileName;
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array {
        return [];
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return '';
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return $this->isBufferable;
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromChunkWriter($this->writer, $this->isBufferable);
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return new FileWriterFromStringWriter($this->buildStringWriter($context));
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return new DOMWriterFromStringWriter($this->buildStringWriter($context));
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->writer;
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return new StringWriterFromChunkWriter($this->writer);
    }
}
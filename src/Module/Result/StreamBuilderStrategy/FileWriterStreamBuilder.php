<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Adapter\ChunkWriterFromFileWriter;
use Slothsoft\Core\IO\Writable\Adapter\DOMWriterFromFileWriter;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromFileWriter;
use Slothsoft\Core\IO\Writable\Adapter\StringWriterFromFileWriter;
use Slothsoft\Farah\Module\Result\ResultInterface;
use SplFileInfo;

class FileWriterStreamBuilder implements FileWriterInterface, StreamBuilderStrategyInterface {
    
    private FileWriterInterface $writer;
    
    private string $fileName;
    
    public function __construct(FileWriterInterface $writer, string $fileName) {
        $this->writer = $writer;
        $this->fileName = $fileName;
    }
    
    private ?FileInfo $file;
    
    public function toFile(): SplFileInfo {
        return $this->file ??= $this->writer->toFile();
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
        return stat($this->toFile()->getRealPath());
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return md5_file($this->toFile()->getRealPath());
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return true;
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return $this->writer instanceof StreamWriterInterface ? $this->writer : new StreamWriterFromFileWriter($this->writer);
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->writer;
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->writer instanceof DOMWriterInterface ? $this->writer : new DOMWriterFromFileWriter($this->writer, (string) $context->createUrl());
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->writer instanceof ChunkWriterInterface ? $this->writer : new ChunkWriterFromFileWriter($this->writer);
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->writer instanceof StringWriterInterface ? $this->writer : new StringWriterFromFileWriter($this->writer);
    }
}
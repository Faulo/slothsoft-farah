<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\MimeTypeDictionary;
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

class FileInfoStreamBuilder implements StreamBuilderStrategyInterface, FileWriterInterface {
    
    private SplFileInfo $file;
    
    private ?string $fileName;
    
    private function exists(): bool {
        return file_exists((string) $this->file);
    }
    
    public function __construct(SplFileInfo $file, ?string $fileName = null) {
        $this->file = $file;
        $this->fileName = $fileName;
    }
    
    public function buildStreamMimeType(ResultInterface $context): string {
        return MimeTypeDictionary::guessMime(pathinfo($this->buildStreamFileName($context), PATHINFO_EXTENSION));
    }
    
    public function buildStreamCharset(ResultInterface $context): string {
        return 'UTF-8';
    }
    
    public function buildStreamFileName(ResultInterface $context): string {
        return $this->fileName ?? $this->file->getFilename();
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array {
        if ($this->exists()) {
            $stat = stat((string) $this->file);
            if ($stat !== false) {
                return $stat;
            }
        }
        
        return [];
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return $this->exists() ? md5_file((string) $this->file) : '';
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return $this->exists();
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromFileWriter($this);
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this;
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return new DOMWriterFromFileWriter($this, (string) $context->createUrl());
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return new ChunkWriterFromFileWriter($this);
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return new StringWriterFromFileWriter($this);
    }
    
    public function toFile(): SplFileInfo {
        return $this->file;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Decorators\ChunkWriterMemoryCache;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Core\IO\Writable\Decorators\FileWriterMemoryCache;
use Slothsoft\Core\IO\Writable\Decorators\StreamWriterMemoryCache;
use Slothsoft\Core\IO\Writable\Decorators\StringWriterMemoryCache;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;

class Result implements ResultInterface {
    
    private ExecutableInterface $ownerExecutable;
    
    private FarahUrlStreamIdentifier $type;
    
    private ResultStrategies $strategies;
    
    private ?StringWriterInterface $stringWriter = null;
    
    private ?StreamWriterInterface $streamWriter = null;
    
    private ?FileWriterInterface $fileWriter = null;
    
    private ?DOMWriterInterface $domWriter = null;
    
    private ?ChunkWriterInterface $chunkWriter = null;
    
    private ?string $hash = null;
    
    private ?string $mimeType = null;
    
    private ?string $charset = null;
    
    private ?bool $isBufferable = null;
    
    private ?array $fileStat = null;
    
    private ?string $fileName = null;
    
    public function __construct(ExecutableInterface $ownerExecutable, FarahUrlStreamIdentifier $type, ResultStrategies $strategies) {
        $this->ownerExecutable = $ownerExecutable;
        $this->type = $type;
        $this->strategies = $strategies;
    }
    
    public function createUrl(): FarahUrl {
        return $this->ownerExecutable->createUrl($this->type);
    }
    
    public function createRealUrl(): FarahUrl {
        return $this->ownerExecutable->createRealUrl($this->type);
    }
    
    public function lookupStringWriter(): StringWriterInterface {
        if ($this->stringWriter === null) {
            $this->stringWriter = new StringWriterMemoryCache($this->strategies->streamBuilder->buildStringWriter($this));
        }
        return $this->stringWriter;
    }
    
    public function lookupStreamWriter(): StreamWriterInterface {
        if ($this->streamWriter === null) {
            $this->streamWriter = new StreamWriterMemoryCache($this->strategies->streamBuilder->buildStreamWriter($this));
        }
        return $this->streamWriter;
    }
    
    public function lookupFileWriter(): FileWriterInterface {
        if ($this->fileWriter === null) {
            $this->fileWriter = new FileWriterMemoryCache($this->strategies->streamBuilder->buildFileWriter($this));
        }
        return $this->fileWriter;
    }
    
    public function lookupDOMWriter(): DOMWriterInterface {
        if ($this->domWriter === null) {
            $this->domWriter = new DOMWriterMemoryCache($this->strategies->streamBuilder->buildDOMWriter($this));
        }
        return $this->domWriter;
    }
    
    public function lookupChunkWriter(): ChunkWriterInterface {
        if ($this->chunkWriter === null) {
            $this->chunkWriter = new ChunkWriterMemoryCache($this->strategies->streamBuilder->buildChunkWriter($this));
        }
        return $this->chunkWriter;
    }
    
    public function lookupHash(): string {
        if ($this->hash === null) {
            $this->hash = $this->strategies->streamBuilder->buildStreamHash($this);
        }
        return $this->hash;
    }
    
    public function lookupMimeType(): string {
        if ($this->mimeType === null) {
            $this->mimeType = $this->strategies->streamBuilder->buildStreamMimeType($this);
        }
        return $this->mimeType;
    }
    
    public function lookupCharset(): string {
        if ($this->charset === null) {
            $this->charset = $this->strategies->streamBuilder->buildStreamCharset($this);
        }
        return $this->charset;
    }
    
    public function lookupIsBufferable(): bool {
        if ($this->isBufferable === null) {
            $this->isBufferable = $this->strategies->streamBuilder->buildStreamIsBufferable($this);
        }
        return $this->isBufferable;
    }
    
    public function lookupFileStatistics(): array {
        if ($this->fileStat === null) {
            $this->fileStat = $this->strategies->streamBuilder->buildStreamFileStatistics($this);
        }
        return $this->fileStat;
    }
    
    public function lookupFileName(): string {
        if ($this->fileName === null) {
            $this->fileName = $this->strategies->streamBuilder->buildStreamFileName($this);
        }
        return $this->fileName;
    }
    
    public function lookupFileChangeTime(): int {
        return $this->lookupFileStatistics()['mtime'] ?? 0;
    }
    
    public function lookupFileSize(): int {
        return $this->lookupFileStatistics()['size'] ?? 0;
    }
}


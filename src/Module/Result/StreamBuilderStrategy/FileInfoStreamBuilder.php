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
use Slothsoft\Farah\Module\Result\ResultInterface;
use SplFileInfo;
use Slothsoft\Core\IO\Writable\Adapter\StringWriterFromFileWriter;

class FileInfoStreamBuilder implements StreamBuilderStrategyInterface, FileWriterInterface {

    private $file;

    private $fileName;

    public function __construct(SplFileInfo $file, ?string $fileName = null) {
        $this->file = $file;
        $this->fileName = $fileName;
    }

    public function buildStreamMimeType(ResultInterface $context): string {
        return MimeTypeDictionary::guessMime($this->file->getExtension());
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return 'UTF-8';
    }

    public function buildStreamFileName(ResultInterface $context): string {
        return $this->fileName ?? $this->file->getFilename();
    }

    public function buildStreamFileStatistics(ResultInterface $context): array {
        return stat((string) $this->file);
    }

    public function buildStreamHash(ResultInterface $context): string {
        return md5_file((string) $this->file);
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return true;
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromFileWriter($context->lookupFileWriter());
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this;
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return new DOMWriterFromFileWriter($context->lookupFileWriter(), (string) $context->createUrl());
    }

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return new ChunkWriterFromFileWriter($context->lookupFileWriter());
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return new StringWriterFromFileWriter($context->lookupFileWriter());
    }

    public function toFile(): SplFileInfo {
        return $this->file;
    }
}


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

class FileWriterStreamBuilder implements StreamBuilderStrategyInterface {

    private $writer;

    private $fileName;

    public function __construct(FileWriterInterface $writer, string $fileName) {
        $this->writer = $writer;
        $this->fileName = $fileName;
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
        return true;
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromFileWriter($context->lookupFileWriter());
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->writer;
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
}
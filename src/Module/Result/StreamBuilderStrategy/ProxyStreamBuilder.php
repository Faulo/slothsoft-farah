<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

class ProxyStreamBuilder implements StreamBuilderStrategyInterface {

    private $proxy;

    public function __construct(ResultInterface $proxy) {
        $this->proxy = $proxy;
    }

    public function buildStreamMimeType(ResultInterface $context): string {
        return $this->proxy->lookupMimeType();
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return $this->proxy->lookupCharset();
    }

    public function buildStreamFileName(ResultInterface $context): string {
        return $this->proxy->lookupFileName();
    }

    public function buildStreamFileStatistics(ResultInterface $context): array {
        return $this->proxy->lookupFileStatistics();
    }

    public function buildStreamHash(ResultInterface $context): string {
        return $this->proxy->lookupHash();
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return $this->proxy->lookupIsBufferable();
    }

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->proxy->lookupChunkWriter();
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->proxy->lookupFileWriter();
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return $this->proxy->lookupStreamWriter();
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->proxy->lookupDOMWriter();
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->proxy->lookupStringWriter();
    }
}


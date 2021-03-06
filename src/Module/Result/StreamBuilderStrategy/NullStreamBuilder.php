<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Module\Result\ResultInterface;

class NullStreamBuilder implements StreamBuilderStrategyInterface {

    public function buildStreamMimeType(ResultInterface $context): string {
        return 'text/plain';
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return '';
    }

    public function buildStreamFileName(ResultInterface $context): string {
        return 'null.txt';
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

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        throw new HttpStatusException('', StatusCode::STATUS_NO_CONTENT);
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        throw new HttpStatusException('', StatusCode::STATUS_NO_CONTENT);
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        throw new HttpStatusException('', StatusCode::STATUS_NO_CONTENT);
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        throw new HttpStatusException('', StatusCode::STATUS_NO_CONTENT);
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        throw new HttpStatusException('', StatusCode::STATUS_NO_CONTENT);
    }
}


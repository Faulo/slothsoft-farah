<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Farah\Module\Result\ResultInterface;

class NullStreamBuilder implements StreamBuilderStrategyInterface
{

    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new Stream(BlobUrl::createTemporaryObject());
    }

    public function buildStreamMimeType(ResultInterface $context): string
    {
        return 'text/plain';
    }

    public function buildStreamCharset(ResultInterface $context): string
    {
        return '';
    }

    public function buildStreamFileName(ResultInterface $context): string
    {
        return 'null.txt';
    }

    public function buildStreamChangeTime(ResultInterface $context): int
    {
        return 0;
    }

    public function buildStreamHash(ResultInterface $context): string
    {
        return '';
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return true;
    }
}


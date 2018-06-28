<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterStreamBuilder implements StreamBuilderStrategyInterface
{

    private $writer;

    private $resourceUrl;

    public function __construct(DOMWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new LazyOpenStream($this->toResourceUrl(), StreamWrapperInterface::MODE_OPEN_READONLY);
    }

    public function buildStreamMimeType(ResultInterface $context): string
    {
        return 'application/xml';
    }

    public function buildStreamCharset(ResultInterface $context): string
    {
        return 'UTF-8';
    }

    public function buildStreamFileName(ResultInterface $context): string
    {
        return 'result.xml';
    }

    public function buildStreamFileStatistics(ResultInterface $context): array
    {
        return [];
    }

    public function buildStreamHash(ResultInterface $context): string
    {
        return md5_file($this->toResourceUrl());
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return true;
    }

    private function toResourceUrl()
    {
        if ($this->resourceUrl === null) {
            $this->resourceUrl = BlobUrl::createTemporaryURL();
            $this->writer->toDocument()->save($this->resourceUrl, LIBXML_NSCLEAN);
        }
        return $this->resourceUrl;
    }
}


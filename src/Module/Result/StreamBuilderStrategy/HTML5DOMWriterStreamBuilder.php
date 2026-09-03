<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use DOMDocument;
use Masterminds\HTML5;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\Delegates\StringWriterFromStringDelegate;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * Stream builder strategy that serializes a DOM writer as HTML5.
 *
 * @author Daniel Schulz
 * @since 2026-09-03
 */
final class HTML5DOMWriterStreamBuilder implements StreamBuilderStrategyInterface, DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;

    private DOMWriterInterface $writer;

    private StringWriterStreamBuilder $streamBuilder;

    public function __construct(DOMWriterInterface $writer, string $documentName = 'document') {
        $this->writer = $writer;
        $serializer = new HTML5();
        $stringWriter = new StringWriterFromStringDelegate(function () use ($serializer): string {
            return $serializer->saveHTML($this->toDocument());
        });
        $this->streamBuilder = new StringWriterStreamBuilder($stringWriter, $documentName, 'html');
    }

    public function toDocument(): DOMDocument {
        return $this->writer->toDocument();
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->streamBuilder->buildStringWriter($context);
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return $this->streamBuilder->buildStreamWriter($context);
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->streamBuilder->buildFileWriter($context);
    }

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->streamBuilder->buildChunkWriter($context);
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->writer;
    }

    public function buildStreamFileStatistics(ResultInterface $context): array {
        return $this->streamBuilder->buildStreamFileStatistics($context);
    }

    public function buildStreamFileName(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamFileName($context);
    }

    public function buildStreamMimeType(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamMimeType($context);
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamCharset($context);
    }

    public function buildStreamHash(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamHash($context);
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return $this->streamBuilder->buildStreamIsBufferable($context);
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Adapter\ChunkWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\FileWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromStringWriter;
use Slothsoft\Core\IO\Writable\Adapter\StringWriterFromDOMWriter;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterStreamBuilder implements StreamBuilderStrategyInterface {

    private $writer;

    private $documentName;

    public function __construct(DOMWriterInterface $writer, string $documentName = 'document') {
        $this->writer = $writer;
        $this->documentName = $documentName;
    }

    public function buildStreamMimeType(ResultInterface $context): string {
        return MimeTypeDictionary::guessMime(pathinfo($context->lookupFileName(), PATHINFO_EXTENSION));
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return $context->lookupDOMWriter()->toDocument()->encoding ?? 'UTF-8';
    }

    public function buildStreamFileName(ResultInterface $context): string {
        $extension = DOMHelper::guessExtension((string) $context->lookupDOMWriter()->toDocument()->documentElement->namespaceURI);
        return "$this->documentName.$extension";
    }

    public function buildStreamFileStatistics(ResultInterface $context): array {
        return [];
    }

    public function buildStreamHash(ResultInterface $context): string {
        return md5($context->lookupStringWriter()->toString());
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return true;
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromStringWriter($context->lookupStringWriter());
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return new FileWriterFromStringWriter($context->lookupStringWriter());
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->writer;
    }

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return new ChunkWriterFromStringWriter($context->lookupStringWriter());
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return new StringWriterFromDOMWriter($context->lookupDOMWriter());
    }
}


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
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\Result\ResultInterface;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterStreamBuilder implements StreamBuilderStrategyInterface, DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $writer;
    
    private string $documentName;
    
    public function __construct(DOMWriterInterface $writer, string $documentName = 'document') {
        $this->writer = $writer;
        $this->documentName = $documentName;
    }
    
    private ?DOMDocument $document;
    
    public function toDocument(): DOMDocument {
        return $this->document ??= $this->writer->toDocument();
    }
    
    private ?string $namespace = null;
    
    private function getNamespace(): string {
        if ($this->namespace === null) {
            if ($root = $this->toDocument()->documentElement) {
                $this->namespace = $root->namespaceURI ?? '';
            } else {
                $this->namespace = '';
            }
        }
        return $this->namespace;
    }
    
    private ?string $extension = null;
    
    private function getExtension(): string {
        return $this->extension ??= DOMHelper::guessExtension($this->getNamespace());
    }
    
    public function buildStreamMimeType(ResultInterface $context): string {
        $extension = $this->getExtension();
        return MimeTypeDictionary::guessMime($extension);
    }
    
    public function buildStreamCharset(ResultInterface $context): string {
        return $this->toDocument()->encoding ?? 'UTF-8';
    }
    
    public function buildStreamFileName(ResultInterface $context): string {
        $extension = $this->getExtension();
        return "$this->documentName.$extension";
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array {
        return [];
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return md5($this->buildStringWriter($context)->toString());
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return true;
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return $this->writer instanceof StreamWriterInterface ? $this->writer : new StreamWriterFromStringWriter($this->buildStringWriter($context));
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->writer instanceof FileWriterInterface ? $this->writer : new FileWriterFromStringWriter($this->buildStringWriter($context));
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->writer;
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->writer instanceof ChunkWriterInterface ? $this->writer : new ChunkWriterFromStringWriter($this->buildStringWriter($context));
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->writer instanceof StringWriterInterface ? $this->writer : new StringWriterFromDOMWriter($this->writer);
    }
}


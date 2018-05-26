<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

class FileWriterStreamBuilder implements StreamBuilderStrategyInterface
{
    
    private $writer;
    
    private $resourceUrl;
    
    public function __construct(FileWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    
    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new LazyOpenStream($this->toResourceUrl(), StreamWrapperInterface::MODE_OPEN_READONLY);
    }
    
    public function buildStreamMimeType(ResultInterface $context): string
    {
        return MimeTypeDictionary::guessMime(pathinfo($this->buildStreamFileName($context), PATHINFO_EXTENSION));
    }
    
    public function buildStreamCharset(ResultInterface $context): string
    {
        return 'UTF-8';
    }
    
    public function buildStreamFileName(ResultInterface $context): string
    {
        return $this->writer->toFile()->getName();
    }
    
    public function buildStreamChangeTime(ResultInterface $context): int
    {
        return filemtime($this->toResourceUrl());
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
            $this->resourceUrl = $this->writer->toFile()->getPath();
        }
        return $this->resourceUrl;
    }
}
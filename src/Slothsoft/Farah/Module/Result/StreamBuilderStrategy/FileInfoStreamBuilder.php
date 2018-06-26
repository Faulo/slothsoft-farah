<?php
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use SplFileInfo;

class FileInfoStreamBuilder implements StreamBuilderStrategyInterface
{
    private $file;
    
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }
    
    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new LazyOpenStream((string) $this->file, StreamWrapperInterface::MODE_OPEN_READONLY);
    }
    
    public function buildStreamMimeType(ResultInterface $context): string
    {
        return MimeTypeDictionary::guessMime($this->file->getExtension());
    }
    
    public function buildStreamCharset(ResultInterface $context): string
    {
        return 'UTF-8';
    }
    
    public function buildStreamFileName(ResultInterface $context): string
    {
        return $this->file->getFilename();
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array
    {
        return stat((string) $this->file);
    }
    
    public function buildStreamHash(ResultInterface $context): string
    {
        return md5_file((string) $this->file);
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return true;
    }
}


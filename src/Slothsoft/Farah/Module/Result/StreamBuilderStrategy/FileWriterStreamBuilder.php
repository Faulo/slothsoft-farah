<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use SplFileInfo;

class FileWriterStreamBuilder implements StreamBuilderStrategyInterface
{

    private $writer;

    private $file;

    public function __construct(FileWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new LazyOpenStream((string) $this->getFile(), StreamWrapperInterface::MODE_OPEN_READONLY);
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
        return $this->writer->toFileName();
    }

    public function buildStreamFileStatistics(ResultInterface $context): array
    {
        return stat((string) $this->getFile());
    }

    public function buildStreamHash(ResultInterface $context): string
    {
        return md5_file((string) $this->getFile());
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return true;
    }

    private function getFile(): SplFileInfo
    {
        if ($this->file === null) {
            $this->file = $this->writer->toFile();
        }
        return $this->file;
    }
}
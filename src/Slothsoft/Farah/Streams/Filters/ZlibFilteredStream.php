<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams\Filters;

use Psr\Http\Message\StreamInterface;

class ZlibFilteredStream extends FilteredStreamBase
{

    private $zlibCoding;

    private $compressor;

    public function __construct(StreamInterface $stream, int $zlibCoding)
    {
        parent::__construct($stream);
        $this->zlibCoding = $zlibCoding;
    }

    protected function processHeader(): string
    {
        $this->compressor = deflate_init($this->zlibCoding);
        return '';
    }

    protected function processPayload(string $data): string
    {
        return deflate_add($this->compressor, $data, ZLIB_NO_FLUSH);
    }

    protected function processFooter(): string
    {
        return deflate_add($this->compressor, '', ZLIB_FINISH);
    }
}


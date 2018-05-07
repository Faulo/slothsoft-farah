<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams\Filters;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

class ZlibFilteredStreamFactory implements FilteredStreamWriterInterface
{

    private $zlibCoding;

    public function __construct(int $zlibCoding)
    {
        $this->zlibCoding = $zlibCoding;
    }

    public function toFilteredStream(StreamInterface $stream): StreamInterface
    {
        return new ZlibFilteredStream($stream, $this->zlibCoding);
    }
}


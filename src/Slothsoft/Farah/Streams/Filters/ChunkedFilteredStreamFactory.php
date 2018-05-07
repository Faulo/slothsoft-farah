<?php
namespace Slothsoft\Farah\Streams\Filters;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

class ChunkedFilteredStreamFactory implements FilteredStreamWriterInterface
{
    public function toFilteredStream(StreamInterface $stream) : StreamInterface
    {
        return new ChunkedFilteredStream($stream);
    }
}


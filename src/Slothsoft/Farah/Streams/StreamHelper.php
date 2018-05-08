<?php
namespace Slothsoft\Farah\Streams;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;

class StreamHelper
{
    public static function cacheStream(StreamInterface $input) : StreamInterface {
        $cache = BlobUrl::createTemporaryObject();
        while (! $input->eof()) {
            fwrite($cache, $input->read($chunkSize));
        }
        rewind($cache);
        return new Stream($cache);
    }
}


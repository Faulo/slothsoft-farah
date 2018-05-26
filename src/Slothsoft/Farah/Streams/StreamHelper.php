<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\IO\Memory;

class StreamHelper
{

    public static function cacheStream(StreamInterface $input, $chunkSize = Memory::ONE_KILOBYTE): StreamInterface
    {
        $cache = BlobUrl::createTemporaryObject();
        while (! $input->eof()) {
            fwrite($cache, $input->read($chunkSize));
        }
        rewind($cache);
        return new Stream($cache);
    }

    public static function sliceStream(StreamInterface $input, int $offset, int $length): StreamInterface
    {
        $input = self::cacheStream($input);
        $cache = BlobUrl::createTemporaryObject();
        $input->seek($offset);
        $totalLength = 0;
        while (! $input->eof() and $totalLength < $length) {
            $dataLength = $length - $totalLength;
            $data = $input->read($dataLength);
            $readLength = strlen($data);
            if ($readLength > $dataLength) {
                fwrite($cache, substr($data, 0, $dataLength));
                $totalLength += $dataLength;
            } else {
                fwrite($cache, $data);
                $totalLength += $readLength;
            }
        }
        rewind($cache);
        return new Stream($cache);
    }
}


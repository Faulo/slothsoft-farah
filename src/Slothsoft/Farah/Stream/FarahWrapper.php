<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Stream;

class FarahWrapper
{

    private $stream;

    // general
    public function stream_open(string $path, string $mode, int $options, &$opened_path)
    {
        $this->stream = StreamFactory::createStream($path, $mode, $options);
        
        // echo $path . PHP_EOL;
        
        return $this->stream !== null;
    }

    public function stream_stat()
    {
        return [];
    }

    public function url_stat()
    {
        return [];
    }

    // InputStreamInterface
    public function stream_read(int $count): string
    {
        return $this->stream->stream_read($count);
    }

    public function stream_tell(): int
    {
        return $this->stream->stream_tell();
    }

    public function stream_eof(): bool
    {
        return $this->stream->stream_eof();
    }

    public function stream_seek(int $offset, int $whence): int
    {
        return $this->stream->stream_seek($offset, $whence);
    }

    // OutputStreamInterface
    public function stream_write(string $data): int
    {
        return $this->stream->stream_write($data);
    }
}
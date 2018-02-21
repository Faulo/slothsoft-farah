<?php
namespace Slothsoft\Farah\Stream\Streams;

use Slothsoft\Farah\Stream\InputStreamInterface;
use Slothsoft\Farah\Stream\OutputStreamInterface;

class FileStream implements InputStreamInterface, OutputStreamInterface
{

    private $handle;

    public function __construct($path, $mode)
    {
        $this->handle = fopen($path, $mode);
    }

    public function stream_read(int $count): string
    {
        return fread($this->handle, $count);
    }

    public function stream_tell(): int
    {
        return ftell($this->handle);
    }

    public function stream_eof(): bool
    {
        return feof($this->handle);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->handle, $offset, $whence);
    }

    public function stream_write(string $data): int
    {
        return fwrite($this->handle, $data);
    }
}
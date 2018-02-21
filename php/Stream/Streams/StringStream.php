<?php
namespace Slothsoft\Farah\Stream\Streams;

use Slothsoft\Farah\Stream\InputStreamInterface;

class StringStream implements InputStreamInterface
{

    private $content;

    private $contentLength;

    private $position;

    public function __construct($content)
    {
        $this->content = $content;
        $this->contentLength = strlen($this->content);
        $this->position = 0;
    }

    public function stream_read(int $count): string
    {
        $tmp = $this->position;
        $this->position += $count;
        return substr($this->content, $tmp, $count);
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position >= $this->contentLength;
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): int
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                return 0;
            case SEEK_CUR:
                $this->position += $offset;
                return 0;
            case SEEK_END:
                $this->position = $this->contentLength + $offset;
                return 0;
            default:
                return - 1;
        }
    }
}
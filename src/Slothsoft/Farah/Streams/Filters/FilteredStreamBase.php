<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams\Filters;

use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Memory;
use BadMethodCallException;
use RuntimeException;

abstract class FilteredStreamBase implements StreamInterface
{
    use StreamDecoratorTrait;

    const STATE_OPENING = 1;

    const STATE_PROCESSING = 2;

    const STATE_CLOSING = 3;

    const STATE_CLOSED = 4;

    private $stream;

    private $state;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = new CachingStream($stream);
        $this->state = static::STATE_OPENING;
    }

    public function read($length)
    {
        switch ($this->state) {
            case static::STATE_OPENING:
                $this->state = static::STATE_PROCESSING;
                return $this->processHeader();
            case static::STATE_PROCESSING:
                $data = $this->processPayload($this->stream->read($length));
                if ($this->stream->eof()) {
                    $this->state = static::STATE_CLOSING;
                }
                return $data;
            case static::STATE_CLOSING:
                $this->state = static::STATE_CLOSED;
                return $this->processFooter();
            case static::STATE_CLOSED:
                throw new RuntimeException('The stream has been closed.');
        }
    }

    public function getContents()
    {
        $buffer = '';
        while (! $this->eof()) {
            $buffer .= $this->read(Memory::ONE_KILOBYTE);
        }
        return $buffer;
    }

    public function eof()
    {
        return $this->state === static::STATE_CLOSED;
    }

    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    public function getSize()
    {
        if ($this->isSeekable()) {
            $ret = strlen($this->getContents());
            $this->rewind();
            return $ret;
        } else {
            return null;
        }
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if ($offset === 0 and $whence === SEEK_SET) {
            $this->stream->rewind();
            $this->state = static::STATE_OPENING;
        } else {
            throw new BadMethodCallException('FilteredStreams only support full rewind.');
        }
    }

    abstract protected function processHeader(): string;

    abstract protected function processPayload(string $data): string;

    abstract protected function processFooter(): string;
}


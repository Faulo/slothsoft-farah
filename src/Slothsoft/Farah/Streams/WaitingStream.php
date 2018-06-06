<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;
use BadMethodCallException;

class WaitingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $stream;

    private $usleep;

    private $heartbeat;

    public function __construct(StreamInterface $stream, int $waitInMicroseconds, array $heartbeat = null)
    {
        $this->stream = $stream;
        $this->usleep = $waitInMicroseconds;
        $this->heartbeat = $heartbeat;
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        $timeWaited = 0;
        while (! $this->stream->eof()) {
            $content = $this->stream->read($length);
            if ($content !== '') {
                return $content;
            }
            usleep($this->usleep);
            
            if ($this->heartbeat) {
                $timeWaited += $this->usleep;
                if ($timeWaited > $this->heartbeat['interval']) {
                    return $this->heartbeat['content'];
                }
            }
        }
        return '';
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new BadMethodCallException('Cannot seek a WaitingStream.');
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new BadMethodCallException('Cannot write a WaitingStream.');
    }
}


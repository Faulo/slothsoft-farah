<?php
namespace Slothsoft\Farah\Streams\Filters;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Memory;

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
        $this->stream = $stream;
        $this->state = static::STATE_OPENING;
    }
    
    public function read($length) {
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
    
    public function getContents() {
        $buffer = '';
        while (!$this->eof()) {
            $buffer .= $this->read(Memory::ONE_KILOBYTE);
        }
        return $buffer;
    }
    
    public function eof() {
        return $this->state === static::STATE_CLOSED;
    }
    
    public function isSeekable() {
        return false;
    }
    
    abstract protected function processHeader(): string;
    abstract protected function processPayload(string $data): string;
    abstract protected function processFooter(): string;
}


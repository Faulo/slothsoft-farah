<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Virtual entity containing repeating 0123456789 bytes, including at huge offsets.
 * The read budget turns accidental whole-entity buffering into a useful failure.
 */
final class RangeEntityStream implements StreamInterface {
    use StreamDecoratorTrait;
    
    private StreamInterface $stream;
    
    private int $position = 0;
    
    private int $bytesRead = 0;
    
    private int $seeks = 0;
    
    public function __construct(private readonly int $size, private readonly int $readBudget = 4096) {
        $this->stream = Utils::streamFor('');
    }
    
    public function getSize(): int {
        return $this->size;
    }
    
    public function getBytesRead(): int {
        return $this->bytesRead;
    }
    
    public function tell(): int {
        return $this->position;
    }
    
    public function eof(): bool {
        return $this->position >= $this->size;
    }
    
    public function isReadable(): bool {
        return $this->stream->isReadable();
    }
    
    public function isSeekable(): bool {
        return true;
    }
    
    public function seek($offset, $whence = SEEK_SET): void {
        if (! in_array($whence, [SEEK_SET, SEEK_CUR, SEEK_END], true)) {
            throw new InvalidArgumentException('Invalid seek mode.');
        }
        if (++$this->seeks > 64) {
            throw new RuntimeException('Virtual stream seek budget exceeded.');
        }
        $base = match ($whence) {
            SEEK_CUR => $this->position,
            SEEK_END => $this->size,
            default => 0
        };
        if ($offset < -$base || $offset > PHP_INT_MAX - $base) {
            throw new RuntimeException('Seek offset is outside the supported interval.');
        }
        $this->position = $base + $offset;
    }
    
    public function read($length): string {
        if ($length < 0) {
            throw new InvalidArgumentException('Read length must be a non-negative integer.');
        }
        if ($length === 0 || $this->eof()) {
            return '';
        }
        $length = min($length, $this->size - $this->position);
        if ($length > $this->readBudget - $this->bytesRead) {
            throw new RuntimeException('Range processing tried to buffer or discard the whole virtual entity.');
        }
        $firstDigit = $this->position % 10;
        $content = substr(str_repeat('0123456789', intdiv($length + 19, 10)), $firstDigit, $length);
        $this->position += $length;
        $this->bytesRead += $length;
        return $content;
    }
}

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
 * Decimal positions avoid floats and require no large files or allocations.
 * The read budget turns accidental whole-entity buffering into a useful failure.
 */
final class RangeEntityStream implements StreamInterface {
    use StreamDecoratorTrait;
    
    private StreamInterface $stream;
    
    private string $size;
    
    private string $position = '0';
    
    private int $bytesRead = 0;
    
    private int $seeks = 0;
    
    public function __construct(string $size, private int $readBudget = 4096) {
        $this->size = $size;
        $this->stream = Utils::streamFor('');
    }
    
    public function getSize(): ?int {
        return self::compare($this->size, (string) PHP_INT_MAX) <= 0 ? (int) $this->size : null;
    }
    
    public function tell(): int {
        if (self::compare($this->position, (string) PHP_INT_MAX) > 0) {
            throw new RuntimeException('Virtual stream position does not fit a PSR-7 integer.');
        }
        return (int) $this->position;
    }
    
    public function eof(): bool {
        return self::compare($this->position, $this->size) >= 0;
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
            default => '0'
        };
        if ($offset < 0) {
            $distance = substr((string) $offset, 1);
            if (self::compare($base, $distance) < 0) {
                throw new RuntimeException('Cannot seek before the beginning of the virtual entity.');
            }
            $this->position = self::subtract($base, $distance);
        } else {
            $this->position = self::add($base, $offset);
        }
    }
    
    public function read($length): string {
        if ($length < 0) {
            throw new InvalidArgumentException('Read length must be a non-negative integer.');
        }
        if ($length === 0 || $this->eof()) {
            return '';
        }
        $remaining = self::subtract($this->size, $this->position);
        $length = self::compare($remaining, (string) $length) < 0 ? (int) $remaining : $length;
        if ($length > $this->readBudget - $this->bytesRead) {
            throw new RuntimeException('Range processing tried to buffer or discard the whole virtual entity.');
        }
        $firstDigit = (int) substr($this->position, -1);
        $content = substr(str_repeat('0123456789', intdiv($length + 19, 10)), $firstDigit, $length);
        $this->position = self::add($this->position, $length);
        $this->bytesRead += $length;
        return $content;
    }
    
    private static function compare(string $left, string $right): int {
        return strlen($left) <=> strlen($right) ?: strcmp($left, $right);
    }
    
    private static function add(string $value, int $increment): string {
        for ($i = strlen($value) - 1; $i >= 0; $i--) {
            $digit = (int) $value[$i] + $increment % 10;
            $increment = intdiv($increment, 10);
            if ($digit >= 10) {
                $digit -= 10;
                $increment++;
            }
            $value[$i] = (string) $digit;
        }
        return ($increment > 0 ? (string) $increment : '') . $value;
    }
    
    private static function subtract(string $left, string $right): string {
        $right = str_pad($right, strlen($left), '0', STR_PAD_LEFT);
        $borrow = 0;
        for ($i = strlen($left) - 1; $i >= 0; $i--) {
            $digit = (int) $left[$i] - (int) $right[$i] - $borrow;
            $borrow = $digit < 0 ? 1 : 0;
            $left[$i] = (string) ($digit < 0 ? $digit + 10 : $digit);
        }
        return ltrim($left, '0') ?: '0';
    }
}

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;
use OverflowException;
use RuntimeException;
use Throwable;

use Psr\Http\Message\StreamInterface;

/**
 * Lazy, forward-only, read-only view of a resolved range. Owns its source.
 * Non-seekable sources must initially be positioned at the representation start.
 * @internal
 */
final class RangeStream implements StreamInterface {
    private const CHUNK_SIZE = 8192;
    
    private ByteCount $position;
    
    private bool $positioned = false;
    
    private bool $closed = false;
    
    public function __construct(private readonly StreamInterface $source, private readonly ByteRange $range) {
        $this->position = new ByteCount(0);
    }
    
    public function __toString(): string {
        try {
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }
    
    public function close(): void {
        $this->closed = true;
        $this->source->close();
    }
    
    public function detach() {
        $this->closed = true;
        return $this->source->detach();
    }
    
    public function getSize(): ?int {
        if ($this->closed) {
            return null;
        }
        try {
            return $this->range->getLength()->toInt();
        } catch (OverflowException) {
            return null;
        }
    }
    
    public function tell(): int {
        $this->assertReadable();
        try {
            return $this->position->toInt();
        } catch (OverflowException $e) {
            throw new RuntimeException('The range position exceeds PHP_INT_MAX.', 0, $e);
        }
    }
    
    public function eof(): bool {
        return $this->closed || $this->position->compare($this->range->getLength()) >= 0;
    }
    
    public function isSeekable(): bool {
        return false;
    }
    
    public function seek($offset, $whence = SEEK_SET): void {
        throw new RuntimeException('Range streams are forward-only.');
    }
    
    public function rewind(): void {
        $this->seek(0);
    }
    
    public function isWritable(): bool {
        return false;
    }
    
    public function write($string): int {
        throw new RuntimeException('Range streams are read-only.');
    }
    
    public function isReadable(): bool {
        return ! $this->closed && $this->source->isReadable();
    }
    
    /** @param mixed $length */
    public function read($length): string {
        $this->assertReadable();
        if (! is_int($length) || $length < 0) {
            throw new InvalidArgumentException('Read length must be a nonnegative integer.');
        }
        if ($length === 0 || $this->eof()) {
            return '';
        }
        if (! $this->positioned) {
            $this->positionSource();
            $this->positioned = true;
        }
        $remaining = $this->range->getLength()->subtract($this->position);
        $length = $this->boundedLength($remaining, min($length, self::CHUNK_SIZE));
        $data = $this->readSource($length);
        $this->position = $this->position->add(new ByteCount(strlen($data)));
        return $data;
    }
    
    public function getContents(): string {
        $result = '';
        while (! $this->eof()) {
            $result .= $this->read(self::CHUNK_SIZE);
        }
        $this->assertReadable();
        return $result;
    }
    
    public function getMetadata($key = null) {
        return $this->source->getMetadata($key);
    }
    
    private function assertReadable(): void {
        if (! $this->isReadable()) {
            throw new RuntimeException('The range stream is not readable.');
        }
    }
    
    private function boundedLength(ByteCount $remaining, int $limit): int {
        return $remaining->compare(new ByteCount($limit)) < 0 ? $remaining->toInt() : $limit;
    }
    
    private function readSource(int $length): string {
        $data = $this->source->read($length);
        if ($data === '') {
            throw new RuntimeException($this->source->eof() ? 'Unexpected end of range source.' : 'Range source made no read progress.');
        }
        if (strlen($data) > $length) {
            throw new RuntimeException('Range source exceeded the requested read length.');
        }
        return $data;
    }
    
    private function positionSource(): void {
        $offset = $this->range->start;
        if ($this->source->isSeekable()) {
            try {
                $this->source->seek($offset->toInt());
                return;
            } catch (RuntimeException) {
                // A failed seek is not assumed to leave the cursor unchanged.
            }
            try {
                $this->source->seek(-$this->range->size->subtract($offset)->toInt(), SEEK_END);
                return;
            } catch (RuntimeException) {
                // A near-end range can usually be positioned with one native seek.
            }
            $this->source->seek(0);
            try {
                while (! $offset->isZero()) {
                    $step = $this->boundedLength($offset, PHP_INT_MAX);
                    $this->source->seek($step, SEEK_CUR);
                    $offset = $offset->subtract(new ByteCount($step));
                }
                return;
            } catch (RuntimeException) {
                $this->source->seek(0);
                $offset = $this->range->start;
            }
        }
        while (! $offset->isZero()) {
            $data = $this->readSource($this->boundedLength($offset, self::CHUNK_SIZE));
            $offset = $offset->subtract(new ByteCount(strlen($data)));
        }
    }
}

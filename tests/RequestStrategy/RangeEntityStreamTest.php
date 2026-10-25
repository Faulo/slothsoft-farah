<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Checks the virtual entity itself so its failures cannot masquerade as range bugs. */
final class RangeEntityStreamTest extends TestCase {
    
    public function testFiniteEntityAndEndOfStream(): void {
        $stream = new RangeEntityStream('10');
        $this->assertSame(10, $stream->getSize());
        $this->assertSame('0123456789', $stream->read(20));
        $this->assertSame(10, $stream->tell());
        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->read(1));
    }
    
    public function testReadCrossesDecimalBoundary(): void {
        $stream = new RangeEntityStream('200');
        $stream->seek(99);
        $this->assertSame('901', $stream->read(3));
        $this->assertSame(102, $stream->tell());
    }
    
    public function testPositionAboveNativeIntegerLimitRemainsExact(): void {
        $stream = new RangeEntityStream('1000000000000000000000000000000');
        $this->assertNull($stream->getSize());
        $stream->seek(PHP_INT_MAX);
        $stream->seek(2, SEEK_CUR);
        $this->assertSame('9012', $stream->read(4));
        $this->assertFalse($stream->eof());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not fit a PSR-7 integer');
        $stream->tell();
    }
    
    public function testReadBudgetPreventsUnboundedBuffering(): void {
        $stream = new RangeEntityStream('1000000000000000000000000000000', 8);
        $this->assertSame('01234567', $stream->read(8));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('whole virtual entity');
        $stream->read(1);
    }
    
    public function testSuffixCanBeReadWithoutRepresentingItsAbsoluteOffsetAsAnInteger(): void {
        $stream = new RangeEntityStream('10000000000000000010');
        $stream->seek(-4, SEEK_END);
        $this->assertSame('6789', $stream->read(4));
        $this->assertTrue($stream->eof());
    }
}

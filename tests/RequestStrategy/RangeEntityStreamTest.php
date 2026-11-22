<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Checks the virtual entity itself so its failures cannot masquerade as range bugs. */
final class RangeEntityStreamTest extends TestCase {
    
    public function testFiniteEntityAndEndOfStream(): void {
        $stream = new RangeEntityStream(10);
        $this->assertSame(10, $stream->getSize());
        $this->assertSame('0123456789', $stream->read(20));
        $this->assertSame(10, $stream->tell());
        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->read(1));
    }
    
    public function testReadCrossesDecimalBoundary(): void {
        $stream = new RangeEntityStream(200);
        $stream->seek(99);
        $this->assertSame('901', $stream->read(3));
        $this->assertSame(102, $stream->tell());
    }
    
    public function testReadBudgetPreventsUnboundedBuffering(): void {
        $stream = new RangeEntityStream(PHP_INT_MAX, 8);
        $this->assertSame('01234567', $stream->read(8));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('whole virtual entity');
        $stream->read(1);
    }
    
    public function testSuffixCanBeReadAtLargeOffset(): void {
        $stream = new RangeEntityStream(PHP_INT_MAX - PHP_INT_MAX % 10);
        $stream->seek(-4, SEEK_END);
        $this->assertSame('6789', $stream->read(4));
        $this->assertTrue($stream->eof());
    }
}

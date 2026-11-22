<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Farah\RequestStrategy\RangeEntityStream;

final class RangeStreamTest extends TestCase {
    public function testLazyBoundedSliceDoesNotReadWholeEntity(): void {
        $source = new RangeEntityStream(PHP_INT_MAX, 4);
        $stream = new RangeStream($source, new ByteRange(2, 5, PHP_INT_MAX));
        $this->assertSame(0, $source->getBytesRead());
        $this->assertSame(4, $stream->getSize());
        $this->assertSame('', $stream->read(0));
        $this->assertSame(0, $source->getBytesRead());
        $this->assertSame('23', $stream->read(2));
        $this->assertSame(2, $stream->tell());
        $this->assertSame('45', $stream->read(99));
        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->read(1));
        $this->assertSame(4, $source->getBytesRead());
    }
    
    public function testNearEndOfHugeEntityUsesNativeEndRelativeSeek(): void {
        $size = PHP_INT_MAX - PHP_INT_MAX % 10;
        $source = new RangeEntityStream($size, 4);
        $decorated = FnStream::decorate($source, ['seek' => static function (int $offset, int $whence = SEEK_SET) use ($source): void {
            if ($whence !== SEEK_END) {
                throw new RuntimeException('Only end-relative seeks are supported.');
            }
            $source->seek($offset, $whence);
        }]);
        $stream = new RangeStream($decorated, new ByteRange($size - 4, $size - 1, $size));
        $this->assertSame('6789', $stream->getContents());
        $this->assertSame(4, $stream->tell());
        $this->assertSame(4, $source->getBytesRead());
    }
    
    public function testLargeOffsetCanUseRelativeSeek(): void {
        $size = PHP_INT_MAX - PHP_INT_MAX % 10;
        $start = $size - 8;
        $source = new RangeEntityStream($size, 4);
        $steps = [];
        $decorated = FnStream::decorate($source, ['seek' => static function (int $offset, int $whence = SEEK_SET) use ($source, &$steps): void {
            if ($whence === SEEK_END || ($whence === SEEK_SET && $offset !== 0)) {
                throw new RuntimeException('Only rewinding and relative seeks are supported.');
            }
            $steps[] = [$offset, $whence];
            $source->seek($offset, $whence);
        }]);
        $stream = new RangeStream($decorated, new ByteRange($start, $start + 3, $size));
        $this->assertSame('2345', $stream->getContents());
        $this->assertSame([[0, SEEK_SET], [$start, SEEK_CUR]], $steps);
        $this->assertSame(4, $source->getBytesRead());
    }
    
    public function testMaximumLengthReadsInSmallChunks(): void {
        $source = new RangeEntityStream(PHP_INT_MAX, 8192);
        $stream = new RangeStream($source, new ByteRange(0, PHP_INT_MAX - 1, PHP_INT_MAX));
        $this->assertSame(PHP_INT_MAX, $stream->getSize());
        $this->assertSame(0, $stream->tell());
        $this->assertSame(8192, strlen($stream->read(PHP_INT_MAX)));
        $this->assertSame(8192, $stream->tell());
        $this->assertFalse($stream->eof());
    }
    
    public function testFailedSeeksResetBeforeReadDiscardAndHonorShortReads(): void {
        $source = Utils::streamFor('0123456789');
        $source->seek(8);
        $decorated = FnStream::decorate($source, [
            'seek' => static function (int $offset, int $whence = SEEK_SET) use ($source): void {
                if ($offset === 0 && $whence === SEEK_SET) {
                    $source->rewind();
                    return;
                }
                $source->seek(9); // Failure may have moved the underlying cursor.
                throw new RuntimeException('Seek failed.');
            },
            'read' => static fn(int $length): string => $source->read(min(2, $length))
        ]);
        $stream = new RangeStream($decorated, new ByteRange(4, 7, 10));
        $this->assertSame('4567', $stream->getContents());
    }
    
    /** @dataProvider failedReadProvider */
    public function testSourceFailureIsReported(bool $eof, string $message): void {
        $source = FnStream::decorate(Utils::streamFor(''), [
            'isSeekable' => static fn(): bool => false,
            'eof' => static fn(): bool => $eof,
            'read' => static fn(int $length): string => ''
        ]);
        $stream = new RangeStream($source, new ByteRange(0, 2, 3));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $stream->getContents();
    }
    
    /**
     * @return iterable<array{0: bool, 1: string}>
     */
    public function failedReadProvider(): iterable {
        yield [true, 'Unexpected end'];
        yield [false, 'no read progress'];
    }
    
    public function testPrematureEndWhileDiscardingIsReported(): void {
        $source = FnStream::decorate(Utils::streamFor('01'), ['isSeekable' => static fn(): bool => false]);
        $stream = new RangeStream($source, new ByteRange(4, 5, 10));
        $this->expectException(RuntimeException::class);
        $stream->read(2);
    }
    
    public function testCloseClosesSourceAndDisablesReading(): void {
        $source = Utils::streamFor('0123');
        $stream = new RangeStream($source, new ByteRange(1, 2, 4));
        $stream->close();
        $this->assertFalse($source->isReadable());
        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertNull($stream->getSize());
        $this->expectException(RuntimeException::class);
        $stream->read(1);
    }
    
    public function testDetachTransfersSourceOwnership(): void {
        $source = Utils::streamFor('0123');
        $stream = new RangeStream($source, new ByteRange(1, 2, 4));
        $resource = $stream->detach();
        $this->assertIsResource($resource);
        $this->assertFalse($source->isReadable());
        $this->assertFalse($stream->isReadable());
        fclose($resource);
    }
    
    /**
     * @dataProvider unsupportedOperationProvider
     * @param list<int|string> $arguments
     */
    public function testRangeIsReadOnlyAndForwardOnly(string $method, array $arguments): void {
        $stream = new RangeStream(Utils::streamFor('0123'), new ByteRange(1, 2, 4));
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->expectException(RuntimeException::class);
        $stream->$method(...$arguments);
    }
    
    /**
     * @return iterable<array{string, list<int|string>}>
     */
    public function unsupportedOperationProvider(): iterable {
        yield ['seek', [0]];
        yield ['rewind', []];
        yield ['write', ['x']];
    }
}

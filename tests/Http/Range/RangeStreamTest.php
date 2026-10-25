<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use RuntimeException;

use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\RequestStrategy\RangeEntityStream;

final class RangeStreamTest extends TestCase {
    private function range(int|string $start, int|string $end, int|string $size): ByteRange {
        return new ByteRange(new ByteCount($start), new ByteCount($end), new ByteCount($size));
    }
    
    public function testLazyBoundedSliceDoesNotReadWholeEntity(): void {
        $source = new RangeEntityStream('10000000000000000010', 4);
        $stream = new RangeStream($source, $this->range(2, 5, '10000000000000000010'));
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
        $source = new RangeEntityStream('10000000000000000010', 4);
        $stream = new RangeStream($source, $this->range('10000000000000000006', '10000000000000000009', '10000000000000000010'));
        $this->assertSame('6789', $stream->getContents());
        $this->assertSame(4, $stream->tell());
        $this->assertSame(4, $source->getBytesRead());
    }
    
    public function testHugeOffsetCanUseBoundedRelativeSeeks(): void {
        $source = new RangeEntityStream('30000000000000000000', 4);
        $steps = [];
        $decorated = FnStream::decorate($source, ['seek' => static function (int $offset, int $whence = SEEK_SET) use ($source, &$steps): void {
            if ($whence === SEEK_END) {
                throw new RuntimeException('End-relative seeks are not supported.');
            }
            $steps[] = [$offset, $whence];
            $source->seek($offset, $whence);
        }]);
        $stream = new RangeStream($decorated, $this->range('10000000000000000002', '10000000000000000005', '30000000000000000000'));
        $this->assertSame('2345', $stream->getContents());
        $this->assertContains([PHP_INT_MAX, SEEK_CUR], $steps);
        $this->assertSame(4, $source->getBytesRead());
    }
    
    public function testOversizedLengthHasNoNativeSizeAndReadsInSmallChunks(): void {
        $source = new RangeEntityStream('10000000000000000010', 8192);
        $stream = new RangeStream($source, $this->range(0, '10000000000000000009', '10000000000000000010'));
        $this->assertNull($stream->getSize());
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
        $stream = new RangeStream($decorated, $this->range(4, 7, 10));
        $this->assertSame('4567', $stream->getContents());
    }
    
    /** @dataProvider failedReadProvider */
    public function testSourceFailureIsReported(bool $eof, string $message): void {
        $source = FnStream::decorate(Utils::streamFor(''), [
            'isSeekable' => static fn(): bool => false,
            'eof' => static fn(): bool => $eof,
            'read' => static fn(int $length): string => ''
        ]);
        $stream = new RangeStream($source, $this->range(0, 2, 3));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $stream->getContents();
    }
    
    public function failedReadProvider(): iterable {
        yield [true, 'Unexpected end'];
        yield [false, 'no read progress'];
    }
    
    public function testPrematureEndWhileDiscardingIsReported(): void {
        $source = FnStream::decorate(Utils::streamFor('01'), ['isSeekable' => static fn(): bool => false]);
        $stream = new RangeStream($source, $this->range(4, 5, 10));
        $this->expectException(RuntimeException::class);
        $stream->read(2);
    }
    
    public function testCloseClosesSourceAndDisablesReading(): void {
        $source = Utils::streamFor('0123');
        $stream = new RangeStream($source, $this->range(1, 2, 4));
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
        $stream = new RangeStream($source, $this->range(1, 2, 4));
        $resource = $stream->detach();
        $this->assertIsResource($resource);
        $this->assertFalse($source->isReadable());
        $this->assertFalse($stream->isReadable());
        fclose($resource);
    }
    
    /** @dataProvider unsupportedOperationProvider */
    public function testRangeIsReadOnlyAndForwardOnly(string $method, array $arguments): void {
        $stream = new RangeStream(Utils::streamFor('0123'), $this->range(1, 2, 4));
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->expectException(RuntimeException::class);
        $stream->$method(...$arguments);
    }
    
    public function unsupportedOperationProvider(): iterable {
        yield ['seek', [0]];
        yield ['rewind', []];
        yield ['write', ['x']];
    }
}

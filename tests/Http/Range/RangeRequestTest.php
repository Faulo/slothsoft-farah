<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;

use PHPUnit\Framework\TestCase;

final class RangeRequestTest extends TestCase {
    public function testMultipleRangesResolveInOrderAndOmitUnsatisfiableMembers(): void {
        $request = RangeRequest::parse('BYTES= 2-99, 99-, -4, , 0-0, -0');
        $this->assertNotNull($request);
        $this->assertSame(5, $request->getRangeCount());
        $ranges = $request->resolve(new ByteCount(10));
        $this->assertSame(['bytes 2-9/10', 'bytes 6-9/10', 'bytes 0-0/10'], array_map(static fn(ByteRange $range): string => $range->getContentRange(), $ranges));
        $this->assertSame(['8', '4', '1'], array_map(static fn(ByteRange $range): string => (string) $range->getLength(), $ranges));
        $this->assertSame([], $request->resolve(new ByteCount(0)));
    }
    
    public function testLargeRangeLengthIsExact(): void {
        $request = RangeRequest::parse('bytes=1-10000000000000000002');
        $this->assertNotNull($request);
        $range = $request->resolve(new ByteCount('10000000000000000010'))[0];
        $this->assertSame('10000000000000000002', (string) $range->getLength());
        $this->assertSame('bytes 1-10000000000000000002/10000000000000000010', $range->getContentRange());
    }
    
    /** @dataProvider invalidProvider */
    public function testInvalidRequestsAreIgnored(string $header): void {
        $this->assertNull(RangeRequest::parse($header));
    }
    
    public function invalidProvider(): iterable {
        foreach (['items=0-1', 'bytes=', 'bytes=,,,', 'bytes=-', 'bytes=2-1', 'bytes=1-2,x', 'bytes=+1-2', 'bytes=1.0-2', "bytes=0-1\n", 'bytes=0 - 1'] as $value) {
            yield [$value];
        }
    }
    
    /** @dataProvider invalidRangeProvider */
    public function testResolvedRangeMustFitRepresentation(int $start, int $end, int $size): void {
        $this->expectException(InvalidArgumentException::class);
        new ByteRange(new ByteCount($start), new ByteCount($end), new ByteCount($size));
    }
    
    public function invalidRangeProvider(): iterable {
        yield [2, 1, 10];
        yield [0, 10, 10];
        yield [0, 0, 0];
    }
}

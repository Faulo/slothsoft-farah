<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/**
 * Response-level range contract, intentionally exposing current implementation gaps.
 * @see RequestStrategyBase
 * @see https://www.rfc-editor.org/rfc/rfc9110.html#section-14
 */
final class RequestStrategyBaseRangeTest extends TestCase {
    
    private const URL = 'farah://test@http-ranges/entity';
    
    private const CONTENT = '0123456789';
    
    private const MODIFIED = 1_700_000_000;
    
    // These are decimal strings, never PHP_INT_MAX + 1 (which produces a float).
    private const HUGE_SIZE = '10000000000000000010';
    
    /**
     * @dataProvider singleRangeProvider
     * @runInSeparateProcess
     */
    public function testSingleRange(?string $range, int $status, ?string $contentRange, string $expected): void {
        $response = $this->request($range === null ? [] : ['Range' => $range]);
        $this->assertRepresentation($response, $status, $contentRange, $expected);
    }
    
    public function singleRangeProvider(): iterable {
        yield 'no range' => [null, 200, null, self::CONTENT];
        yield 'first byte' => ['bytes=0-0', 206, 'bytes 0-0/10', '0'];
        yield 'last byte' => ['bytes=9-9', 206, 'bytes 9-9/10', '9'];
        yield 'bounded range' => ['bytes=2-5', 206, 'bytes 2-5/10', '2345'];
        yield 'whole representation' => ['bytes=0-9', 206, 'bytes 0-9/10', self::CONTENT];
        yield 'open end' => ['bytes=4-', 206, 'bytes 4-9/10', '456789'];
        yield 'open end from zero' => ['bytes=0-', 206, 'bytes 0-9/10', self::CONTENT];
        yield 'end at entity length' => ['bytes=2-10', 206, 'bytes 2-9/10', '23456789'];
        yield 'end beyond entity length' => ['bytes=2-99', 206, 'bytes 2-9/10', '23456789'];
        yield 'suffix' => ['bytes=-4', 206, 'bytes 6-9/10', '6789'];
        yield 'one byte suffix' => ['bytes=-1', 206, 'bytes 9-9/10', '9'];
        yield 'suffix equals entity length' => ['bytes=-10', 206, 'bytes 0-9/10', self::CONTENT];
        yield 'suffix exceeds entity length' => ['bytes=-11', 206, 'bytes 0-9/10', self::CONTENT];
        yield 'leading zeroes' => ['bytes=0002-0005', 206, 'bytes 2-5/10', '2345'];
        yield 'case insensitive unit' => ['BYTES=2-5', 206, 'bytes 2-5/10', '2345'];
        yield 'list whitespace' => ['bytes= 2-5', 206, 'bytes 2-5/10', '2345'];
        yield 'end at PHP integer limit' => ['bytes=2-' . PHP_INT_MAX, 206, 'bytes 2-9/10', '23456789'];
        yield 'end above PHP integer limit' => ['bytes=2-1000000000000000000000000000000', 206, 'bytes 2-9/10', '23456789'];
        yield 'suffix above PHP integer limit' => ['bytes=-1000000000000000000000000000000', 206, 'bytes 0-9/10', self::CONTENT];
        yield 'two hundred digit end' => ['bytes=2-' . str_repeat('9', 200), 206, 'bytes 2-9/10', '23456789'];
        yield 'two hundred digit suffix' => ['bytes=-' . str_repeat('9', 200), 206, 'bytes 0-9/10', self::CONTENT];
    }
    
    /**
     * @dataProvider unsatisfiableRangeProvider
     * @runInSeparateProcess
     */
    public function testUnsatisfiableRange(string $range): void {
        $response = $this->request(['Range' => $range]);
        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame('bytes */10', $response->getHeaderLine('Content-Range'));
        $this->assertNotSame(self::CONTENT, (string) $response->getBody());
    }
    
    public function unsatisfiableRangeProvider(): iterable {
        yield 'start equals size' => ['bytes=10-'];
        yield 'start beyond size' => ['bytes=11-15'];
        yield 'zero length suffix' => ['bytes=-0'];
        yield 'start at PHP integer limit' => ['bytes=' . PHP_INT_MAX . '-'];
        yield 'start above PHP integer limit' => ['bytes=1000000000000000000000000000000-'];
        yield 'both endpoints above PHP integer limit' => ['bytes=1000000000000000000000000000000-1000000000000000000000000000001'];
        yield 'two hundred digit start' => ['bytes=' . str_repeat('9', 200) . '-'];
    }
    
    /**
     * HTTP permits ignoring or rejecting malformed range syntax. It must not
     * turn malformed input into an unrelated successful partial response.
     * @dataProvider malformedRangeProvider
     * @runInSeparateProcess
     */
    public function testMalformedRangeIsIgnoredOrRejected(string $range): void {
        $response = $this->request(['Range' => $range]);
        $this->assertContains($response->getStatusCode(), [200, 400, 416]);
        if ($response->getStatusCode() === 200) {
            $this->assertRepresentation($response, 200, null, self::CONTENT);
        } else {
            $this->assertNotSame(self::CONTENT, (string) $response->getBody());
        }
    }
    
    public function malformedRangeProvider(): iterable {
        yield 'empty set' => ['bytes='];
        yield 'both endpoints absent' => ['bytes=-'];
        yield 'descending endpoints' => ['bytes=7-3'];
        yield 'non decimal start' => ['bytes=a-4'];
        yield 'non decimal end' => ['bytes=2-a'];
        yield 'signed start' => ['bytes=+2-4'];
        yield 'fractional endpoint' => ['bytes=2-4.5'];
        yield 'extra hyphen' => ['bytes=1-2-3'];
        yield 'trailing garbage' => ['bytes=2-4junk'];
        yield 'missing hyphen' => ['bytes=3'];
        yield 'invalid member in set' => ['bytes=0-1,foo'];
    }
    
    /**
     * @dataProvider ignoredRangeProvider
     * @runInSeparateProcess
     */
    public function testRangeIsIgnoredWhenNotApplicable(string $method, string $range): void {
        $response = $this->request(['Range' => $range], $method);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Content-Range'));
        $this->assertSame('10', $response->getHeaderLine('Content-Length'));
        $this->assertSame($method === 'HEAD' ? '' : self::CONTENT, (string) $response->getBody());
    }
    
    public function ignoredRangeProvider(): iterable {
        yield 'unknown range unit' => ['GET', 'items=2-5'];
        yield 'HEAD ignores satisfiable range' => ['HEAD', 'bytes=2-5'];
        yield 'HEAD ignores unsatisfiable range' => ['HEAD', 'bytes=20-'];
        yield 'POST ignores range' => ['POST', 'bytes=2-5'];
        yield 'OPTIONS ignores range' => ['OPTIONS', 'bytes=2-5'];
    }
    
    /**
     * @dataProvider ifRangeProvider
     * @runInSeparateProcess
     */
    public function testIfRange(string $validator, bool $matches): void {
        $response = $this->request(['Range' => 'bytes=2-5', 'If-Range' => $validator]);
        $this->assertRepresentation($response, $matches ? 206 : 200, $matches ? 'bytes 2-5/10' : null, $matches ? '2345' : self::CONTENT);
    }
    
    public function ifRangeProvider(): iterable {
        yield 'matching strong ETag' => ['"range-entity"', true];
        yield 'different ETag' => ['"old-entity"', false];
        yield 'weak ETag cannot match' => ['W/"range-entity"', false];
        yield 'matching modification date' => [gmdate('D, d M Y H:i:s \\G\\M\\T', self::MODIFIED), true];
        yield 'stale modification date' => [gmdate('D, d M Y H:i:s \\G\\M\\T', self::MODIFIED - 60), false];
        yield 'invalid validator' => ['not a validator', false];
    }
    
    /**
     * Preconditions must be evaluated before attempting to slice the body.
     * @dataProvider conditionalRangeProvider
     * @runInSeparateProcess
     */
    public function testNotModifiedTakesPrecedenceOverRange(array $headers, string $range): void {
        $response = $this->request($headers + ['Range' => $range]);
        $this->assertSame(304, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Content-Range'));
        $this->assertSame('', (string) $response->getBody());
    }
    
    public function conditionalRangeProvider(): iterable {
        yield 'ETag with valid range' => [['If-None-Match' => '"range-entity"'], 'bytes=2-5'];
        yield 'weak ETag with valid range' => [['If-None-Match' => 'W/"range-entity"'], 'bytes=2-5'];
        yield 'ETag list with valid range' => [['If-None-Match' => '"old-entity", "range-entity"'], 'bytes=2-5'];
        yield 'ETag wildcard with valid range' => [['If-None-Match' => '*'], 'bytes=2-5'];
        yield 'ETag with unsatisfiable range' => [['If-None-Match' => '"range-entity"'], 'bytes=20-'];
        yield 'date with valid range' => [['If-Modified-Since' => gmdate('D, d M Y H:i:s \\G\\M\\T', self::MODIFIED)], 'bytes=2-5'];
        yield 'date with overflowing range' => [['If-Modified-Since' => gmdate('D, d M Y H:i:s \\G\\M\\T', self::MODIFIED)], 'bytes=2-1000000000000000000000000000000'];
    }
    
    /**
     * @dataProvider multipleRangeProvider
     * @runInSeparateProcess
     */
    public function testMultipleRangesAreServedOrIgnored(string $range): void {
        $response = $this->request(['Range' => $range]);
        $this->assertContains($response->getStatusCode(), [200, 206]);
        if ($response->getStatusCode() === 200) {
            $this->assertRepresentation($response, 200, null, self::CONTENT);
            return;
        }
        
        $body = (string) $response->getBody();
        if ($response->hasHeader('Content-Range')) {
            // HTTP permits a subset of the ranges or coalescing nearby ranges.
            $contentRange = $response->getHeaderLine('Content-Range');
            $expected = ['bytes 0-1/10' => '01', 'bytes 8-9/10' => '89', 'bytes 0-9/10' => self::CONTENT];
            $this->assertArrayHasKey($contentRange, $expected);
            $this->assertSame($expected[$contentRange], $body);
        } else {
            $type = $response->getHeaderLine('Content-Type');
            $this->assertMatchesRegularExpression('/^multipart\/byteranges;\s*boundary=(?:"[^"]+"|[^;\s]+)$/i', $type);
            preg_match('/boundary=(?:"([^"]+)"|([^;\s]+))/i', $type, $matches);
            $boundary = $matches[1] !== '' ? $matches[1] : $matches[2];
            $this->assertStringEndsWith("--$boundary--", rtrim($body, "\r\n"));
            $parts = explode("--$boundary", $body);
            $this->assertSame('', trim(array_shift($parts), "\r\n"));
            array_pop($parts);
            $expected = ['bytes 0-1/10' => '01', 'bytes 8-9/10' => '89', 'bytes 0-9/10' => self::CONTENT];
            $actual = [];
            foreach ($parts as $part) {
                $this->assertStringStartsWith("\r\n", $part);
                $this->assertStringEndsWith("\r\n", $part);
                $fields = explode("\r\n\r\n", substr($part, 2, -2), 2);
                $this->assertCount(2, $fields);
                $this->assertSame(1, preg_match('/(?:^|\r\n)Content-Range:\s*(bytes \d+-\d+\/10)(?:\r\n|$)/i', $fields[0], $match));
                $this->assertArrayHasKey($match[1], $expected);
                $this->assertArrayNotHasKey($match[1], $actual);
                $actual[$match[1]] = $fields[1];
            }
            $this->assertNotEmpty($actual);
            foreach ($actual as $contentRange => $content) {
                $this->assertSame($expected[$contentRange], $content);
            }
        }
        if ($response->hasHeader('Content-Length')) {
            $this->assertSame((string) strlen($body), $response->getHeaderLine('Content-Length'));
        }
    }
    
    public function multipleRangeProvider(): iterable {
        yield 'two disjoint ranges' => ['bytes=0-1,8-9'];
        yield 'optional list whitespace' => ['bytes=0-1, 8-9'];
        yield 'bounded and suffix' => ['bytes=0-1,-2'];
    }
    
    /** @runInSeparateProcess */
    public function testRangeOffsetsCountBytes(): void {
        $response = $this->request(['Range' => 'bytes=0-1'], 'GET', Utils::streamFor("\0\xffabc"), '5');
        $this->assertRepresentation($response, 206, 'bytes 0-1/5', "\0\xff");
    }
    
    /** @runInSeparateProcess */
    public function testNonSeekableSourceWithShortReads(): void {
        $source = Utils::streamFor(self::CONTENT);
        $stream = FnStream::decorate($source, [
            'isSeekable' => static fn(): bool => false,
            'seek' => static function (): void {
                throw new \RuntimeException('The original source is not seekable.');
            },
            'read' => static fn(int $length): string => $source->read(min(2, $length))
        ]);
        $response = $this->request(['Range' => 'bytes=4-7'], 'GET', $stream);
        $this->assertRepresentation($response, 206, 'bytes 4-7/10', '4567');
    }
    
    /** @runInSeparateProcess */
    public function testBufferableUnknownSizeSourceCanBeRangedAfterCaching(): void {
        $stream = FnStream::decorate(Utils::streamFor(self::CONTENT), ['getSize' => static fn(): ?int => null]);
        $response = $this->request(['Range' => 'bytes=2-5'], 'GET', $stream);
        $this->assertRepresentation($response, 206, 'bytes 2-5/10', '2345');
    }
    
    /**
     * An empty representation may ignore a Range header or reject it.
     * @dataProvider emptyRangeProvider
     * @runInSeparateProcess
     */
    public function testRangesOnEmptyEntity(string $range): void {
        $response = $this->request(['Range' => $range], 'GET', Utils::streamFor(''), '0');
        $this->assertContains($response->getStatusCode(), [200, 416]);
        if ($response->getStatusCode() === 200) {
            $this->assertRepresentation($response, 200, null, '');
        } else {
            $this->assertSame('bytes */0', $response->getHeaderLine('Content-Range'));
        }
    }
    
    public function emptyRangeProvider(): iterable {
        yield 'bounded' => ['bytes=0-0'];
        yield 'open ended' => ['bytes=0-'];
        yield 'suffix' => ['bytes=-1'];
    }
    
    /** @runInSeparateProcess */
    public function testUnknownSizeCanIgnoreRange(): void {
        $stream = FnStream::decorate(Utils::streamFor(self::CONTENT), ['getSize' => static fn(): ?int => null]);
        $response = $this->request(['Range' => 'bytes=2-5'], 'GET', $stream, null, false);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Content-Range'));
        $this->assertFalse($response->hasHeader('Content-Length'));
        $this->assertSame(self::CONTENT, (string) $response->getBody());
    }
    
    /**
     * PSR-7 getSize() remains int|null. The exact size is supplied through the
     * result's file statistics, so these tests expose the missing size plumbing
     * as well as overflow in parsing/slicing. No nonconforming stream is used.
     * @dataProvider largeEntityProvider
     * @runInSeparateProcess
     */
    public function testLargeEntityRange(string $size, string $range, string $contentRange, string $expected): void {
        $stream = new RangeEntityStream($size);
        $this->assertNull($stream->getSize());
        $response = $this->request(['Range' => $range], 'GET', $stream, $size, false);
        $this->assertRepresentation($response, 206, $contentRange, $expected);
    }
    
    public function largeEntityProvider(): iterable {
        yield 'size above PHP_INT_MAX with small endpoints' => [self::HUGE_SIZE, 'bytes=2-5', 'bytes 2-5/' . self::HUGE_SIZE, '2345'];
        yield 'size at 64 bit integer limit plus one' => ['9223372036854775808', 'bytes=2-5', 'bytes 2-5/9223372036854775808', '2345'];
        yield 'start and end above PHP_INT_MAX' => [self::HUGE_SIZE, 'bytes=10000000000000000002-10000000000000000005', 'bytes 10000000000000000002-10000000000000000005/' . self::HUGE_SIZE, '2345'];
        yield 'open range starting above PHP_INT_MAX' => [self::HUGE_SIZE, 'bytes=10000000000000000005-', 'bytes 10000000000000000005-10000000000000000009/' . self::HUGE_SIZE, '56789'];
        yield 'suffix of entity larger than PHP_INT_MAX' => [self::HUGE_SIZE, 'bytes=-4', 'bytes 10000000000000000006-10000000000000000009/' . self::HUGE_SIZE, '6789'];
        yield 'oversized end clipped to oversized size' => [self::HUGE_SIZE, 'bytes=10000000000000000008-9999999999999999999999999999999', 'bytes 10000000000000000008-10000000000000000009/' . self::HUGE_SIZE, '89'];
    }
    
    /** @runInSeparateProcess */
    public function testHeadRetainsExactOversizedEntityLength(): void {
        $response = $this->request([], 'HEAD', new RangeEntityStream(self::HUGE_SIZE), self::HUGE_SIZE, false);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(self::HUGE_SIZE, $response->getHeaderLine('Content-Length'));
        $this->assertSame('', (string) $response->getBody());
    }
    
    private function request(array $headers, string $method = 'GET', ?StreamInterface $stream = null, ?string $size = '10', bool $bufferable = true): ResponseInterface {
        $stream ??= Utils::streamFor(self::CONTENT);
        $writer = $this->createMock(StreamWriterInterface::class);
        $writer->method('toStream')->willReturn($stream);
        $builder = $this->createMock(StreamBuilderStrategyInterface::class);
        $builder->method('buildStreamWriter')->willReturn($writer);
        $builder->method('buildStreamFileName')->willReturn('range-entity.bin');
        $builder->method('buildStreamMimeType')->willReturn('application/octet-stream');
        $builder->method('buildStreamCharset')->willReturn('');
        $builder->method('buildStreamHash')->willReturn('range-entity');
        $builder->method('buildStreamIsBufferable')->willReturn($bufferable);
        $statistics = ['mtime' => self::MODIFIED];
        if ($size !== null) {
            $statistics['size'] = strlen($size) < strlen((string) PHP_INT_MAX) ? (int) $size : $size;
        }
        $builder->method('buildStreamFileStatistics')->willReturn($statistics);
        RangeResponseBuilder::$streamBuilder = $builder;
        Module::registerWithXmlManifestAndDefaultAssets('test@http-ranges', dirname(__DIR__, 2) . '/test-files/http-ranges');
        
        // HTTP/1.0 avoids transfer-coding framing in unknown-size body assertions.
        $request = (new ServerRequest($method, self::URL, $headers))->withProtocolVersion('1.0');
        return (new LookupAssetStrategy())->process($request);
    }
    
    private function assertRepresentation(ResponseInterface $response, int $status, ?string $contentRange, string $body): void {
        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame('bytes', $response->getHeaderLine('Accept-Ranges'));
        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($body), $response->getHeaderLine('Content-Length'));
        if ($contentRange === null) {
            $this->assertFalse($response->hasHeader('Content-Range'));
        } else {
            $this->assertSame($contentRange, $response->getHeaderLine('Content-Range'));
        }
        $this->assertSame($body, (string) $response->getBody());
    }
}

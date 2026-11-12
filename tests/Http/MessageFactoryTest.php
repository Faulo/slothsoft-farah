<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\FarahTesting\Module\AbstractTestCase;

class MessageFactoryTest extends AbstractTestCase {
    
    /** @return iterable<string, array{string}> */
    public static function responseContents(): iterable {
        yield 'empty' => [''];
        yield 'binary' => ["a\0b\xffc"];
        yield 'larger than temporary memory buffer' => [str_repeat('0123456789', 300000)];
    }
    
    /** @dataProvider responseContents */
    public function testContentsRoundTrip(string $contents): void {
        $stream = MessageFactory::createStreamFromContents($contents);
        try {
            $this->assertSame(0, $stream->tell());
            $this->assertSame($contents, $stream->getContents());
        } finally {
            $stream->close();
        }
    }
    
    public function testMissingFileThrows(): void {
        $file = HTTPFile::createFromPath(temp_file(__METHOD__));
        $this->expectException(RuntimeException::class);
        MessageFactory::createStreamFromFile($file);
    }
    
    /** @runInSeparateProcess */
    public function testTemporaryStreamOpenFailureThrows(): void {
        stream_wrapper_unregister('php');
        try {
            $this->expectException(RuntimeException::class);
            MessageFactory::createStreamFromContents('body');
        } finally {
            stream_wrapper_restore('php');
        }
    }
    
    public function testNonBufferableResponseRetainsMarkerThroughPsr7Clones(): void {
        $response = MessageFactory::createServerResponse(200, ['Content-Type' => 'application/octet-stream'], MessageFactory::createStreamFromContents('small'), false);
        $this->assertInstanceOf(StreamingResponse::class, $response->withHeader('Example', 'value')->withStatus(201));
        $this->assertSame('no', $response->getHeaderLine('X-Accel-Buffering'));
        $this->assertSame(5, $response->getBody()->getSize());
    }
    
    public function testSseMediaTypeAndUnknownLengthDoNotImplyStreaming(): void {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(null);
        $response = MessageFactory::createServerResponse(200, ['Content-Type' => 'text/event-stream'], $body);
        $this->assertNotInstanceOf(StreamingResponse::class, $response);
        $this->assertFalse($response->hasHeader('X-Accel-Buffering'));
    }
    
    public function testCreateServerRequest(): void {
        $_SERVER['REQUEST_URI'] = 'farah://slothsoft@farah/?a=b';
        $pageRequest = MessageFactory::createServerRequest();
        $this->assertEquals('a=b', $pageRequest->getUri()
            ->getQuery());
    }
}

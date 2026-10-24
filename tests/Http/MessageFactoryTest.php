<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\FarahTesting\Module\AbstractTestCase;

class MessageFactoryTest extends AbstractTestCase {
    
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
    
    public function testCreateServerRequest() {
        $_SERVER['REQUEST_URI'] = 'farah://slothsoft@farah/?a=b';
        $pageRequest = MessageFactory::createServerRequest();
        $this->assertEquals('a=b', $pageRequest->getUri()
            ->getQuery());
    }
}

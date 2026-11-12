<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * HTTPRequestTest
 *
 * @see HTTPRequest
 */
final class HTTPRequestTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(HTTPRequest::class), "Failed to load class 'Slothsoft\Farah\HTTPRequest'!");
    }
    
    public function testEmptyBodyIsSuccessful(): void {
        $request = new HTTPRequest();
        $this->assertSame('', $request->getBody());
        $this->assertSame('', $request->getInput());
        $this->assertNull($request->getBodyJSON());
        $this->assertNull($request->getInputJSON());
    }
    
    /** @runInSeparateProcess */
    public function testUnavailableBodyPreservesFalseResult(): void {
        $request = new HTTPRequest();
        stream_wrapper_unregister('php');
        try {
            $this->assertFalse($request->getBody());
            $this->assertFalse($request->getInput());
        } finally {
            stream_wrapper_restore('php');
        }
    }
    
    /** @return iterable<string, array{string}> */
    public static function jsonReaders(): iterable {
        yield 'body' => ['getBodyJSON'];
        yield 'legacy input' => ['getInputJSON'];
    }
    
    /**
     * @runInSeparateProcess
     * @dataProvider jsonReaders
     */
    public function testUnavailableJSONBodyThrows(string $reader): void {
        $request = new HTTPRequest();
        stream_wrapper_unregister('php');
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Unable to read request body.');
            $request->$reader();
        } finally {
            stream_wrapper_restore('php');
        }
    }
    
    public function testInitRecognizesHttpsEnvironment(): void {
        $request = new HTTPRequest();
        $request->init([
            'HTTPS' => 'on'
        ]);
        $request->setPath('/page');
        
        $this->assertSame('https://localhost/page', $request->getURL());
    }
}

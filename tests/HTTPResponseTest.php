<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * HTTPResponseTest
 *
 * @see HTTPResponse
 *
 */
final class HTTPResponseTest extends TestCase {
    
    /** @runInSeparateProcess */
    public function testUnavailableOptionalTrackingInputDoesNotAbortResponse(): void {
        $response = new HTTPResponse();
        $response->setBody('response');
        $environment = [];
        stream_wrapper_unregister('php');
        try {
            $response->addTrackingInfo($environment);
        } finally {
            stream_wrapper_restore('php');
        }
        $this->assertArrayNotHasKey('RESPONSE_INPUT', $environment);
        $this->assertSame(8, $environment['RESPONSE_LENGTH']);
        $this->assertSame('response', $response->getBody());
    }
    
    /**
     *
     * @test
     */
    /** @runInSeparateProcess */
    public function testEmptyDocumentWithTimingEnabledRemainsSupported(): void {
        HTTPResponse::setHttpConfig(['doc-timestamp' => true]);
        $response = new HTTPResponse();
        ob_start();
        try {
            $response->setDocument(new DOMDocument());
        } finally {
            ob_end_clean();
        }
        $this->assertStringStartsWith('<?xml', $response->getBody());
    }
    
    /**
     * On Windows the fixture must explicitly be marked sparse before writing.
     *
     * @requires OS Linux
     * @runInSeparateProcess
     */
    public function testSendsRangeNearEndOfOneTerabyteSparseFile(): void {
        if (PHP_INT_SIZE < 8) {
            $this->markTestSkipped('A one-terabyte offset requires 64-bit PHP.');
        }
        $size = 1_000_000_000_000;
        $file = tempnam(sys_get_temp_dir(), 'farah-range-');
        $this->assertIsString($file);
        try {
            $handle = fopen($file, 'wb');
            $this->assertIsResource($handle);
            try {
                $this->assertSame(0, fseek($handle, $size - 4));
                $this->assertSame(4, fwrite($handle, 'test'));
            } finally {
                fclose($handle);
            }
            $response = new HTTPResponse();
            $response->setRange(sprintf('bytes=%d-999999999999999999999999', $size - 4));
            $response->setFile($file, 'asset.bin');
            $this->expectOutputString('test');
            $response->send();
            $this->assertSame(HTTPResponse::STATUS_PARTIAL_CONTENT, $response->getStatus());
        } finally {
            unlink($file);
        }
    }
    
    /** @runInSeparateProcess */
    public function testRangeStartBeyondNativeIntegerLimitIsUnsatisfiable(): void {
        $response = new HTTPResponse();
        $response->setStatus(HTTPResponse::STATUS_OK);
        $response->setBody('body');
        $response->setRange('bytes=' . PHP_INT_MAX . '0-');
        $this->expectOutputString('');
        $response->send();
        $this->assertSame(HTTPResponse::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE, $response->getStatus());
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(HTTPResponse::class), "Failed to load class 'Slothsoft\Farah\HTTPResponse'!");
    }
}

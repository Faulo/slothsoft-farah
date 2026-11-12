<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

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
    public function testClassExists(): void {
        $this->assertTrue(class_exists(HTTPResponse::class), "Failed to load class 'Slothsoft\Farah\HTTPResponse'!");
    }
}
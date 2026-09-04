<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

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
    
    public function testInitRecognizesHttpsEnvironment(): void {
        $request = new HTTPRequest();
        $request->init([
            'HTTPS' => 'on'
        ]);
        $request->setPath('/page');
        
        $this->assertSame('https://localhost/page', $request->getURL());
    }
}

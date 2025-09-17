<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * HTTPRequestTest
 *
 * @see HTTPRequest
 *
 * @todo auto-generated
 */
class HTTPRequestTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(HTTPRequest::class), "Failed to load class 'Slothsoft\Farah\HTTPRequest'!");
    }
}
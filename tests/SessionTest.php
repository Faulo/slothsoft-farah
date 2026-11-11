<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * SessionTest
 *
 * @see Session
 *
 */
final class SessionTest extends TestCase {
    
    /** @runInSeparateProcess */
    public function testNullCookieValuePreservesDefaultLookup(): void {
        $this->assertFalse(headers_sent());
        $session = new Session();
        
        $session->setCookie('farah-test-cookie');
        
        $this->assertArrayHasKey('farah-test-cookie', $_COOKIE);
        $this->assertNull($_COOKIE['farah-test-cookie']);
        $this->assertSame('fallback', $session->getCookie('farah-test-cookie', 'fallback'));
    }
    
    /** @runInSeparateProcess */
    public function testGenerateKeyWithoutRequestMetadata(): void {
        unset($_SERVER['REQUEST_TIME_FLOAT'], $_SERVER['REMOTE_ADDR']);
        $session = (new \ReflectionClass(Session::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Session::class, 'generateKey');
        
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $method->invoke($session));
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Session::class), "Failed to load class 'Slothsoft\Farah\Session'!");
    }
}

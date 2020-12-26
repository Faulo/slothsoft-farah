<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * SessionTest
 *
 * @see Session
 *
 * @todo auto-generated
 */
class SessionTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Session::class), "Failed to load class 'Slothsoft\Farah\Session'!");
    }
}
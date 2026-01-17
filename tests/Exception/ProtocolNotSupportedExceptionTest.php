<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * ProtocolNotSupportedExceptionTest
 *
 * @see ProtocolNotSupportedException
 *
 * @todo auto-generated
 */
class ProtocolNotSupportedExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ProtocolNotSupportedException::class), "Failed to load class 'Slothsoft\Farah\Exception\ProtocolNotSupportedException'!");
    }
}
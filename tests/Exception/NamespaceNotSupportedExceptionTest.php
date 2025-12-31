<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * NamespaceNotSupportedExceptionTest
 *
 * @see NamespaceNotSupportedException
 *
 * @todo auto-generated
 */
final class NamespaceNotSupportedExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(NamespaceNotSupportedException::class), "Failed to load class 'Slothsoft\Farah\Exception\NamespaceNotSupportedException'!");
    }
}
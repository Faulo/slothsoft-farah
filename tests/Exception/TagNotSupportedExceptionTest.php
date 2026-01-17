<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * TagNotSupportedExceptionTest
 *
 * @see TagNotSupportedException
 *
 * @todo auto-generated
 */
class TagNotSupportedExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(TagNotSupportedException::class), "Failed to load class 'Slothsoft\Farah\Exception\TagNotSupportedException'!");
    }
}
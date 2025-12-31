<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * ExceptionContextTest
 *
 * @see ExceptionContext
 *
 * @todo auto-generated
 */
final class ExceptionContextTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ExceptionContext::class), "Failed to load class 'Slothsoft\Farah\Exception\ExceptionContext'!");
    }
}
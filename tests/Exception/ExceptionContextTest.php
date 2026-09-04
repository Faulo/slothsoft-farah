<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * ExceptionContextTest
 *
 * @see ExceptionContext
 */
final class ExceptionContextTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ExceptionContext::class), "Failed to load class 'Slothsoft\Farah\Exception\ExceptionContext'!");
    }

    public function testAppendReusesContext(): void {
        $exception = new Exception('test');

        $expected = ExceptionContext::append($exception, [
            'class' => self::class
        ]);
        $actual = ExceptionContext::append($exception);

        $this->assertSame($expected, $actual);
        $this->assertSame(self::class, $actual->getClass());
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * UndefinedBehaviorExceptionTest
 *
 * @see UndefinedBehaviorException
 *
 * @todo auto-generated
 */
class UndefinedBehaviorExceptionTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(UndefinedBehaviorException::class), "Failed to load class 'Slothsoft\Farah\Exception\UndefinedBehaviorException'!");
    }
}
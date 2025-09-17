<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * ModuleNotFoundExceptionTest
 *
 * @see ModuleNotFoundException
 *
 * @todo auto-generated
 */
class ModuleNotFoundExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ModuleNotFoundException::class), "Failed to load class 'Slothsoft\Farah\Exception\ModuleNotFoundException'!");
    }
}
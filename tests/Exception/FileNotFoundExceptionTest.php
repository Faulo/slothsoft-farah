<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * FileNotFoundExceptionTest
 *
 * @see FileNotFoundException
 *
 * @todo auto-generated
 */
class FileNotFoundExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileNotFoundException::class), "Failed to load class 'Slothsoft\Farah\Exception\FileNotFoundException'!");
    }
}
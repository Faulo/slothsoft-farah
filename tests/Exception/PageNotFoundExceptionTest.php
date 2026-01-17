<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * PageNotFoundExceptionTest
 *
 * @see PageNotFoundException
 *
 * @todo auto-generated
 */
final class PageNotFoundExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(PageNotFoundException::class), "Failed to load class 'Slothsoft\Farah\Exception\PageNotFoundException'!");
    }
}
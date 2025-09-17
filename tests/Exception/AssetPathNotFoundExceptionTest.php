<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;

/**
 * AssetPathNotFoundExceptionTest
 *
 * @see AssetPathNotFoundException
 *
 * @todo auto-generated
 */
class AssetPathNotFoundExceptionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AssetPathNotFoundException::class), "Failed to load class 'Slothsoft\Farah\Exception\AssetPathNotFoundException'!");
    }
}
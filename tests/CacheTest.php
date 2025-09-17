<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * CacheTest
 *
 * @see Cache
 *
 * @todo auto-generated
 */
class CacheTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Cache::class), "Failed to load class 'Slothsoft\Farah\Cache'!");
    }
}
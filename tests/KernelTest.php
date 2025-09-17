<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * KernelTest
 *
 * @see Kernel
 *
 * @todo auto-generated
 */
class KernelTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Kernel::class), "Failed to load class 'Slothsoft\Farah\Kernel'!");
    }
}
<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use PHPUnit\Framework\TestCase;

/**
 * ExecutableTest
 *
 * @see Executable
 *
 * @todo auto-generated
 */
class ExecutableTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Executable::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\Executable'!");
    }
}
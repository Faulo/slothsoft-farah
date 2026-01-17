<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use PHPUnit\Framework\TestCase;

/**
 * ExecutableInterfaceTest
 *
 * @see ExecutableInterface
 *
 * @todo auto-generated
 */
class ExecutableInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ExecutableInterface::class), "Failed to load interface 'Slothsoft\Farah\Module\Executable\ExecutableInterface'!");
    }
}
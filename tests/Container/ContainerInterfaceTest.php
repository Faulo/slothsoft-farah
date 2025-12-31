<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Container;

use PHPUnit\Framework\TestCase;

/**
 * ContainerInterfaceTest
 *
 * @see ContainerInterface
 *
 * @todo auto-generated
 */
final class ContainerInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ContainerInterface::class), "Failed to load interface 'Slothsoft\Farah\Container\ContainerInterface'!");
    }
}
<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use PHPUnit\Framework\TestCase;

/**
 * CodingInterfaceTest
 *
 * @see CodingInterface
 *
 * @todo auto-generated
 */
class CodingInterfaceTest extends TestCase {

    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(CodingInterface::class), "Failed to load interface 'Slothsoft\Farah\Http\CodingInterface'!");
    }
}
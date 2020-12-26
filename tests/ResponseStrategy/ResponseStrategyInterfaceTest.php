<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use PHPUnit\Framework\TestCase;

/**
 * ResponseStrategyInterfaceTest
 *
 * @see ResponseStrategyInterface
 *
 * @todo auto-generated
 */
class ResponseStrategyInterfaceTest extends TestCase {

    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ResponseStrategyInterface::class), "Failed to load interface 'Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface'!");
    }
}
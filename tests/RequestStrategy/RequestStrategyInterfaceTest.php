<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;

/**
 * RequestStrategyInterfaceTest
 *
 * @see RequestStrategyInterface
 *
 * @todo auto-generated
 */
class RequestStrategyInterfaceTest extends TestCase {

    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(RequestStrategyInterface::class), "Failed to load interface 'Slothsoft\Farah\RequestStrategy\RequestStrategyInterface'!");
    }
}
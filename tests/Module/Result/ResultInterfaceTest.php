<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use PHPUnit\Framework\TestCase;

/**
 * ResultInterfaceTest
 *
 * @see ResultInterface
 *
 * @todo auto-generated
 */
final class ResultInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ResultInterface::class), "Failed to load interface 'Slothsoft\Farah\Module\Result\ResultInterface'!");
    }
}
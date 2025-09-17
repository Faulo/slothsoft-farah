<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use PHPUnit\Framework\TestCase;

/**
 * AssetInterfaceTest
 *
 * @see AssetInterface
 *
 * @todo auto-generated
 */
class AssetInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(AssetInterface::class), "Failed to load interface 'Slothsoft\Farah\Module\Asset\AssetInterface'!");
    }
}
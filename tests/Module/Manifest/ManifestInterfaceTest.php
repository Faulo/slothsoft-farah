<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use PHPUnit\Framework\TestCase;

/**
 * ManifestInterfaceTest
 *
 * @see ManifestInterface
 *
 * @todo auto-generated
 */
final class ManifestInterfaceTest extends TestCase {
    
    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(ManifestInterface::class), "Failed to load interface 'Slothsoft\Farah\Module\Manifest\ManifestInterface'!");
    }
}
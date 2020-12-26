<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use PHPUnit\Framework\TestCase;

/**
 * ModuleTest
 *
 * @see Module
 *
 * @todo auto-generated
 */
class ModuleTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Module::class), "Failed to load class 'Slothsoft\Farah\Module\Module'!");
    }
}
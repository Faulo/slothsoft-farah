<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * ModuleTest
 *
 * @see Module
 */
class ModuleTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Module::class), "Failed to load class 'Slothsoft\Farah\Module\Module'!");
    }

    public function testGetBaseUrl(): void {
        $expected = FarahUrl::createFromReference('farah://slothsoft@farah');

        $actual = Module::getBaseUrl();

        $this->assertEquals($expected, $actual);
    }
}
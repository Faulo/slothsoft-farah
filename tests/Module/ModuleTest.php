<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;

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

        Kernel::clearCurrentSitemap();

        $actual = Module::getBaseUrl();

        $this->assertEquals($expected, $actual);
    }

    public function testGetBaseUrlUsesLatestRegisteredModule(): void {
        $expected = FarahUrl::createFromReference('farah://vendor@module');

        Kernel::clearCurrentSitemap();

        Module::register('vendor@module', temp_dir(__CLASS__), $this->createMock(ManifestStrategies::class));

        $actual = Module::getBaseUrl();

        $this->assertEquals($expected, $actual);
    }

    public function testGetBaseUrlUsesKernelSitemap(): void {
        $expected = FarahUrl::createFromReference('farah://slothsoft@core');

        Kernel::setCurrentSitemap('farah://slothsoft@core/');

        $actual = Module::getBaseUrl();

        Kernel::clearCurrentSitemap();

        $this->assertEquals($expected, $actual);
    }
}
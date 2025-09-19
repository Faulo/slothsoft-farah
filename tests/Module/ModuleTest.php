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
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_clearAllCachedAssets_phpinfoPrintsServer(): void {
        $_SERVER['TEST_VARIABLE'] = 'test_clearAllCachedAssets';
        $expected = file_get_contents('farah://slothsoft@farah/phpinfo');
        $this->assertStringContainsString('test_clearAllCachedAssets', $expected);
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_clearAllCachedAssets_isNecessary(): void {
        $_SERVER['TEST_VARIABLE'] = 'test_clearAllCachedAssets expected';
        $expected = file_get_contents('farah://slothsoft@farah/phpinfo');
        $this->assertStringContainsString('test_clearAllCachedAssets expected', $expected);
        
        $_SERVER['TEST_VARIABLE'] = 'test_clearAllCachedAssets actual';
        $actual = file_get_contents('farah://slothsoft@farah/phpinfo');
        $this->assertStringContainsString('test_clearAllCachedAssets expected', $actual);
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_clearAllCachedAssets_works(): void {
        $_SERVER['TEST_VARIABLE'] = 'test_clearAllCachedAssets expected';
        $expected = file_get_contents('farah://slothsoft@farah/phpinfo');
        $this->assertStringContainsString('test_clearAllCachedAssets expected', $expected);
        
        Module::clearAllCachedAssets();
        
        $_SERVER['TEST_VARIABLE'] = 'test_clearAllCachedAssets actual';
        $actual = file_get_contents('farah://slothsoft@farah/phpinfo');
        $this->assertStringContainsString('test_clearAllCachedAssets actual', $actual);
    }
}
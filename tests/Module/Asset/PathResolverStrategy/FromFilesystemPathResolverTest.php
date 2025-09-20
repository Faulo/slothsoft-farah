<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * FromFilesystemPathResolverTest
 *
 * @see FromFilesystemPathResolver
 */
class FromFilesystemPathResolverTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FromFilesystemPathResolver::class), "Failed to load class 'Slothsoft\Farah\Module\Asset\PathResolverStrategy\FromFilesystemPathResolver'!");
    }
    
    /**
     *
     * @dataProvider pathProvider
     */
    public function test_resolvePath(string $url, ?string $exception = null): void {
        if ($exception) {
            $this->expectException($exception);
            Module::resolveToFileWriter(FarahUrl::createFromReference($url));
        } else {
            $file = Module::resolveToFileWriter(FarahUrl::createFromReference($url))->toFile();
            $this->assertFileExists($file->getRealPath());
        }
    }
    
    public function pathProvider(): iterable {
        yield 'valid directory' => [
            'farah://slothsoft@farah/js'
        ];
        
        yield 'valid asset' => [
            'farah://slothsoft@farah/js/DOMHelper'
        ];
        
        yield 'missing asset' => [
            'farah://slothsoft@farah/js/missing-asset',
            AssetPathNotFoundException::class
        ];
    }
}
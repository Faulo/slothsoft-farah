<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\FileNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
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
        $expected = FarahUrl::createFromReference('farah://slothsoft@farah');
        
        Kernel::setCurrentSitemap('farah://slothsoft@farah/');
        
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
    
    /**
     *
     * @runInSeparateProcess
     */
    public function testCanNotLoadMissingModule(): void {
        $authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test');
        Module::registerWithXmlManifestAndDefaultAssets($authority, 'test-files/missing');
        
        $this->expectException(FileNotFoundException::class);
        Module::resolveToResult(FarahUrl::createFromComponents($authority));
    }
    
    private function loadTestModule(string $directory): ManifestInterface {
        $authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test');
        Module::registerWithXmlManifestAndDefaultAssets($authority, $directory);
        return Module::resolveToManifest(FarahUrl::createFromComponents($authority));
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function testCanLoadTestModule(): string {
        $directory = 'test-files' . DIRECTORY_SEPARATOR . 'test-module';
        $manifest = $this->loadTestModule($directory);
        $this->assertNotNull($manifest);
        $this->assertEquals($manifest, Module::resolveToManifest($manifest->createUrl()));
        return $directory;
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider modulePathProvider
     * @depends testCanLoadTestModule
     */
    public function testModuleUsesManifestDirectory(string $path, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $mime = $manifest->lookupAsset($path)
            ->lookupExecutable()
            ->lookupDefaultResult()
            ->lookupMimeType();
        
        $this->assertEquals('application/xml', $mime);
    }
    
    public function modulePathProvider(): iterable {
        yield 'main module' => [
            '/'
        ];
        yield 'main module fragment' => [
            '/test'
        ];
        yield 'sub module' => [
            '/submodule'
        ];
        yield 'sub module fragment' => [
            '/submodule/test'
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function testCanLoadImportModule(): string {
        $directory = 'test-files' . DIRECTORY_SEPARATOR . 'test-import';
        $manifest = $this->loadTestModule($directory);
        $this->assertNotNull($manifest);
        $this->assertEquals($manifest, Module::resolveToManifest($manifest->createUrl()));
        return $directory;
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider importPathProvider
     * @depends testCanLoadImportModule
     */
    public function testModuleCanImportAssets(string $path, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $mime = $manifest->lookupAsset($path)
            ->lookupExecutable()
            ->lookupDefaultResult()
            ->lookupMimeType();
        
        $this->assertEquals('application/xml', $mime);
    }
    
    public function importPathProvider(): iterable {
        $hierarchy = [
            'import' => [
                'test' => []
            ],
            'result-import' => [],
            'result-use-manifest' => [
                'test' => []
            ],
            'result-use-document' => [
                'test' => []
            ]
        ];
        
        function traverse(array $hierarchy, array $segments = []): iterable {
            yield $segments;
            foreach ($hierarchy as $key => $value) {
                yield from traverse($value, [
                    ...$segments,
                    $key
                ]);
            }
        }
        
        foreach (traverse($hierarchy) as $segments) {
            $path = '/' . implode('/', $segments);
            yield $path => [
                $path
            ];
        }
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider importContentProvider
     * @depends testCanLoadImportModule
     */
    public function testModuleDoesImportInfo(string $path, array $elements, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $url = $manifest->createUrl($path);
        
        $document = DOMHelper::loadDocument((string) $url);
        $this->assertNotNull($document, "Failed to load document '$url'");
        $document->formatOutput = true;
        $xpath = DOMHelper::loadXPath($document);
        
        foreach ($elements as $tag => $attributes) {
            foreach ($attributes as $name => $value) {
                $query = sprintf('boolean(//sfm:%s[@%s = "%s"])', $tag, $name, $value);
                
                $result = $xpath->evaluate($query);
                
                $this->assertTrue($result, "Failed to find element '$tag' with $name '$value' in '$url':" . PHP_EOL . $document->saveXML());
            }
        }
    }
    
    public function importContentProvider(): iterable {
        return [
            '/import' => [
                '/import',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/import'
                    ],
                    'manifest-info' => [
                        'url' => 'farah://slothsoft@test/import/test'
                    ]
                ]
            ],
            '/result-import' => [
                '/result-import',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-import'
                    ],
                    'manifest-info' => [
                        'url' => 'farah://slothsoft@test/import/test'
                    ]
                ]
            ],
            '/result-use-manifest' => [
                '/result-use-manifest',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-manifest'
                    ],
                    'manifest-info' => [
                        'url' => 'farah://slothsoft@test/result-use-manifest/test'
                    ]
                ]
            ],
            '/result-use-document' => [
                '/result-use-document',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document/test'
                    ]
                ]
            ],
            '/result-link-stylesheet' => [
                '/result-link-stylesheet',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-link-stylesheet'
                    ],
                    'link-stylesheet' => [
                        'ref' => '/slothsoft@test/result-link-stylesheet/test'
                    ]
                ]
            ],
            '/result-link-script' => [
                '/result-link-script',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-link-script'
                    ],
                    'link-script' => [
                        'ref' => '/slothsoft@test/result-link-script/test'
                    ]
                ]
            ]
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider importUrlProvider
     * @depends testCanLoadImportModule
     */
    public function testModuleCanServeImport(string $expectedPath, string $actualPath, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $expectedUrl = $manifest->createUrl($expectedPath);
        $actualUrl = $manifest->createUrl($actualPath);
        
        $this->assertFileEquals((string) $expectedUrl, (string) $actualUrl);
    }
    
    public function importUrlProvider(): iterable {
        return [
            '/result-use-manifest' => [
                '/import/test',
                '/result-use-manifest/test'
            ],
            '/result-use-document' => [
                '/import/test',
                '/result-use-document/test'
            ],
            '/result-use-document#xml' => [
                '/import/test#xml',
                '/result-use-document/test#xml'
            ],
            '/result-use-template' => [
                '/import/test',
                '/result-use-template/test'
            ],
            '/result-link-stylesheet' => [
                '/import/test',
                '/result-link-stylesheet/test'
            ],
            '/result-link-script' => [
                '/import/test',
                '/result-link-script/test'
            ]
        ];
    }
}
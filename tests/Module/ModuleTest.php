<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\FileNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;
use Slothsoft\Core\ServerEnvironment;

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
        FileSystem::removeDir(ServerEnvironment::getCacheDirectory(), true);
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
                'query' => [],
                'fragment' => []
            ],
            'result-import' => [],
            'result-use-manifest' => [
                'fragment' => []
            ],
            'result-use-document' => [
                'fragment' => []
            ],
            'result-use-document-no-name' => [
                'fragment' => []
            ],
            'result-use-document-no-name-traversal' => [
                'fragment' => []
            ],
            'result-use-document-no-name-root' => [
                'test' => []
            ],
            'result-use-document-no-name-farah' => [
                'farah' => []
            ],
            'result-use-document-renamed' => [
                'renamed' => []
            ],
            'result-use-document-nested' => [
                'nested' => []
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
     * @dataProvider importAttributeProvider
     * @depends testCanLoadImportModule
     */
    public function testModuleDoesNormalizeAttributes(string $path, string $attribute, string $value, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $element = $manifest->lookupAsset($path)->getManifestElement();
        
        $this->assertEquals($value, $element->getAttribute($attribute));
    }
    
    public function importAttributeProvider(): iterable {
        yield 'module name' => [
            '/',
            Manifest::ATTR_NAME,
            'test'
        ];
        
        yield 'module assetpath' => [
            '/',
            Manifest::ATTR_ASSETPATH,
            ''
        ];
        
        yield 'fragment assetpath' => [
            '/import',
            Manifest::ATTR_ASSETPATH,
            '/import'
        ];
        
        yield 'use-manifest ref' => [
            '/result-use-manifest/fragment',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/import/fragment'
        ];
        
        yield 'use-document ref' => [
            '/result-use-document/fragment',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/import/fragment'
        ];
        
        yield 'use-document ref traversal' => [
            '/result-use-document-no-name-traversal/fragment',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/import/fragment'
        ];
        
        yield 'use-document module traversal' => [
            '/result-use-document-no-name-farah/farah',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@farah/'
        ];
        
        yield 'use-manifest url not renamed' => [
            '/result-use-manifest-renamed/renamed',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/import/fragment'
        ];
        
        yield 'use-manifest name renamed' => [
            '/result-use-manifest-renamed/renamed',
            Manifest::ATTR_NAME,
            'renamed'
        ];
        
        yield 'use-document url not renamed' => [
            '/result-use-document-renamed/renamed',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/import/fragment'
        ];
        
        yield 'use-document name renamed' => [
            '/result-use-document-renamed/renamed',
            Manifest::ATTR_NAME,
            'renamed'
        ];
        
        yield 'use-document name nested' => [
            '/result-use-document-nested/nested',
            Manifest::ATTR_NAME,
            'nested'
        ];
        
        yield 'use-document url nested' => [
            '/result-use-document-nested/nested',
            Manifest::ATTR_REFERENCE,
            'farah://slothsoft@test/result-use-document-renamed/renamed'
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider importContentProvider
     * @depends testCanLoadImportModule
     */
    public function testModuleDoesImportInfo(string $path, array $elements, string $manifestDirectory): void {
        $manifest = $this->loadTestModule($manifestDirectory);
        $url = FarahUrl::createFromReference($path, $manifest->createUrl());
        
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
                        'url' => 'farah://slothsoft@test/import/fragment'
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
                        'url' => 'farah://slothsoft@test/import/fragment'
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
                        'url' => 'farah://slothsoft@test/import/fragment'
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
                        'url' => 'farah://slothsoft@test/import/fragment'
                    ],
                    'print-fragment' => [
                        'type' => 'xml'
                    ]
                ]
            ],
            '/result-use-document-no-name' => [
                '/result-use-document',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/fragment'
                    ],
                    'print-fragment' => [
                        'type' => 'xml'
                    ]
                ]
            ],
            '/result-use-document-with-hash' => [
                '/result-use-document-with-hash',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document-with-hash'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/fragment#hash'
                    ],
                    'print-fragment' => [
                        'type' => 'xml'
                    ]
                ]
            ],
            '/result-use-document-with-query' => [
                '/result-use-document-with-query',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document-with-query'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/query?a=1&c'
                    ],
                    'print-query' => [
                        'args' => 'a=1&c'
                    ]
                ]
            ],
            '/result-use-document-with-query-and-arguments' => [
                '/result-use-document-with-query?a=2&d',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document-with-query?a=2&d'
                    ],
                    'param' => [
                        'name' => 'a',
                        'value' => '2'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/query?a=2&c&d'
                    ],
                    'print-query' => [
                        'args' => 'a=2&c&d'
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
                        'ref' => '/slothsoft@test/import/fragment'
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
                        'ref' => '/slothsoft@test/import/fragment'
                    ]
                ]
            ],
            '/result-use-manifest-renamed' => [
                '/result-use-manifest-renamed',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-manifest-renamed'
                    ],
                    'manifest-info' => [
                        'url' => 'farah://slothsoft@test/import/fragment',
                        'name' => 'renamed'
                    ]
                ]
            ],
            '/result-use-document-renamed' => [
                '/result-use-document-renamed',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document-renamed'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/fragment',
                        'name' => 'renamed'
                    ]
                ]
            ],
            '/result-use-document-nested' => [
                '/result-use-document-nested',
                [
                    'fragment-info' => [
                        'url' => 'farah://slothsoft@test/result-use-document-nested'
                    ],
                    'document-info' => [
                        'url' => 'farah://slothsoft@test/import/fragment',
                        'name' => 'nested'
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
        $expectedUrl = FarahUrl::createFromReference($expectedPath, $manifest->createUrl());
        $actualUrl = FarahUrl::createFromReference($actualPath, $manifest->createUrl());
        
        $this->assertFileEquals((string) $expectedUrl, (string) $actualUrl);
    }
    
    public function importUrlProvider(): iterable {
        return [
            '/result-use-manifest' => [
                '/import/fragment',
                '/result-use-manifest/fragment'
            ],
            '/result-use-document' => [
                '/import/fragment',
                '/result-use-document/fragment'
            ],
            '/result-use-template' => [
                '/import/fragment',
                '/result-use-template/fragment'
            ],
            '/result-link-stylesheet' => [
                '/import/fragment',
                '/result-link-stylesheet/fragment'
            ],
            '/result-link-script' => [
                '/import/fragment',
                '/result-link-script/fragment'
            ],
            '/result-use-document-with-hash' => [
                '/import/fragment#hash',
                '/result-use-document-with-hash/fragment'
            ]
        ];
    }
}
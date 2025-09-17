<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\DOMWriter\DOMDocumentDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\NullResultBuilder;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\DefaultAssetBuilder;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;
use DOMDocument;

/**
 * AbstractSitemapTestTest
 *
 * @see AbstractSitemapTest
 */
class AbstractSitemapTestTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractSitemapTest::class), "Failed to load class 'Slothsoft\Farah\ModuleTests\AbstractSitemapTest'!");
    }

    private DOMDocument $sitesDocument;

    private function createSuT(): AbstractSitemapTest {
        $siteXML = <<<EOT
        <?xml version="1.0" encoding="UTF-8"?>
        <domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" name="test.slothsoft.net" vendor="slothsoft" module="test"
        	ref="domain-asset" status-active="" status-public="" sfd:languages="de-de en-us" version="1.1">
        
        	<page name="test-page" ref="page-asset">        
        		<file name="test-file" ref="file-asset" />
        	</page>
        </domain>
        EOT;

        $this->sitesDocument = new DOMDocument();
        $this->sitesDocument->loadXML($siteXML);

        $sitesAsset = $this->createStub(AssetInterface::class);

        $sitesExecutable = new Executable($sitesAsset, FarahUrlArguments::createEmpty(), new ExecutableStrategies(new DOMWriterResultBuilder(new DOMDocumentDOMWriter($this->sitesDocument))));

        $sitesAsset->method('lookupExecutable')->willReturn($sitesExecutable);

        StubSitemapTest::$sitesAsset = $sitesAsset;

        TestCache::instance(StubSitemapTest::class)->clear();

        $treeLoader = $this->createStub(TreeLoaderStrategyInterface::class);
        $treeLoader->method('loadTree')->willReturnCallback(function (ManifestInterface $context): LeanElement {
            $root = LeanElement::createOneFromArray('assets', [], [
                LeanElement::createOneFromArray('custom-asset', [
                    'name' => 'domain-asset',
                    'executable-builder' => StubExecutableBuilder::class
                ]),
                LeanElement::createOneFromArray('fragment', [
                    'name' => 'page-asset',
                    'executable-builder' => StubExecutableBuilder::class
                ]),
                LeanElement::createOneFromArray('fragment', [
                    'name' => 'file-asset',
                    'executable-builder' => StubExecutableBuilder::class
                ])
            ]);
            $context->normalizeManifestTree($root);
            return $root;
        });

        $assetBuilder = new DefaultAssetBuilder();

        $manifest = new ManifestStrategies($treeLoader, $assetBuilder);

        Module::register(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test'), '.', $manifest);

        return new StubSitemapTest();
    }

    /**
     *
     * @runInSeparateProcess
     */
    public function test_getSitesDocument() {
        $sut = $this->createSuT();

        $this->assertEquals($this->sitesDocument, $sut->getSitesDocumentProtected());
    }

    /**
     *
     * @runInSeparateProcess
     */
    public function test_pageNodeProvider() {
        $sut = $this->createSuT();

        $actual = $sut->pageNodeProvider();

        $this->assertEquals([
            '/',
            '/test-page/',
            '/test-page/test-file'
        ], array_keys($actual));
    }

    /**
     *
     * @runInSeparateProcess
     * @dataProvider pageAssetAndLinkProvider
     */
    public function test_pageLinkProvider(string $assetPath, string $assetXML, array $assetLinks) {
        $pageDocument = new DOMDocument();
        $pageDocument->loadXML($assetXML);
        StubExecutableBuilder::$executables[$assetPath] = new DOMWriterResultBuilder(new DOMDocumentDOMWriter($pageDocument));

        $sut = $this->createSuT();

        $actual = $sut->pageLinkProvider();

        $this->assertEquals($assetLinks, $actual);
    }

    public function pageAssetAndLinkProvider(): iterable {
        yield 'Skip links that are pages' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="/" />
            		<a href="/test-page/" />
            		<a href="/test-page/test-file" />
            	</body>
            </html>
            EOT,
            []
        ];

        yield 'HTML header elements' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<head>
            		<link href="." />
            		<link href="" />
            		<script src="." />
            		<script src="" />
            	</head>
            </html>
            EOT,
            [
                '/test-page/ link href .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ link href ' => [
                    '/test-page/',
                    ''
                ],
                '/test-page/ script src .' => [
                    '/test-page/',
                    '.'
                ]
            ]
        ];

        yield 'HTML body elements with required sources' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="." />
            		<a href="" />
            		<img src="." />
            		<img src="" />
            		<iframe src="." />
            		<iframe src="" />
            		<source src="." />
            		<source src="" />
            		<track src="." />
            		<track src="" />
            	</body>
            </html>
            EOT,
            [
                '/test-page/ a href .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ a href ' => [
                    '/test-page/',
                    ''
                ],
                '/test-page/ img src .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ img src ' => [
                    '/test-page/',
                    ''
                ],
                '/test-page/ iframe src .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ iframe src ' => [
                    '/test-page/',
                    ''
                ],
                '/test-page/ source src .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ source src ' => [
                    '/test-page/',
                    ''
                ],
                '/test-page/ track src .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ track src ' => [
                    '/test-page/',
                    ''
                ]
            ]
        ];

        yield 'HTML body elements with optional sources' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<form action="." />
            		<form action="" />
            		<video src="." />
            		<video src="" />
            		<audio src="." />
            		<audio src="" />
            	</body>
            </html>
            EOT,
            [
                '/test-page/ form action .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ video src .' => [
                    '/test-page/',
                    '.'
                ],
                '/test-page/ audio src .' => [
                    '/test-page/',
                    '.'
                ]
            ]
        ];

        yield 'special URIs' => [
            '/page-asset',
            <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml">
            	<body>
            		<a href="mailto:test@email" />
            		<img src="data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==" />
            	</body>
            </html>
            EOT,
            [
                '/test-page/ a href mailto:test@email' => [
                    '/test-page/',
                    'mailto:test@email'
                ],
                '/test-page/ img src data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==' => [
                    '/test-page/',
                    'data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='
                ]
            ]
        ];
    }
}

class StubExecutableBuilder implements ExecutableBuilderStrategyInterface {

    public static array $executables = [];

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies(self::$executables[(string) $context->getUrlPath()] ?? new NullResultBuilder());
    }
}

class StubSitemapTest extends AbstractSitemapTest {

    public static AssetInterface $sitesAsset;

    protected static function loadSitesAsset(): AssetInterface {
        return self::$sitesAsset;
    }

    public function getSitesDocumentProtected(): DOMDocument {
        return $this->getSitesDocument();
    }
}
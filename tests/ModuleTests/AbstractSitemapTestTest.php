<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\CatchAllPathResolver;
use Slothsoft\Farah\Module\DOMWriter\DOMDocumentDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use DOMDocument;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\AssetBuilderStrategyInterface;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\DefaultAssetBuilder;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;

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
                LeanElement::createOneFromArray('fragment', [
                    'name' => 'domain-asset'
                ]),
                LeanElement::createOneFromArray('fragment', [
                    'name' => 'page-asset'
                ]),
                LeanElement::createOneFromArray('fragment', [
                    'name' => 'file-asset'
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

    public function test_getSitesDocument() {
        $sut = $this->createSuT();

        $this->assertEquals($this->sitesDocument, $sut->getSitesDocumentProtected());
    }

    public function test_pageNodeProvider() {
        $sut = $this->createSuT();

        $actual = $sut->pageNodeProvider();

        $this->assertEquals([
            '/',
            '/test-page/',
            '/test-page/test-file'
        ], array_keys($actual));
    }

    public function test_pageLinkProvider() {
        $sut = $this->createSuT();

        $actual = $sut->pageLinkProvider();

        $this->assertEquals([], $actual);
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
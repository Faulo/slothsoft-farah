<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\DOMWriter\DOMDocumentDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
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
        <domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" name="test.slothsoft.net" vendor="slothsoft" module="test.slothsoft.net"
        	ref="pages/domain" status-active="" status-public="" sfd:languages="de-de en-us" version="1.1">
        
        	<page name="test-page" ref="pages/page">        
        		<file name="test-file" ref="pages/file" />
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

        return new StubSitemapTest();
    }

    public function testGetSitesDocument() {
        $sut = $this->createSuT();

        $this->assertEquals($this->sitesDocument, $sut->getSitesDocumentProtected());
    }

    public function testPageNodeProvider() {
        $sut = $this->createSuT();

        $actual = $sut->pageNodeProvider();

        $this->assertEquals([
            '/',
            '/test-page/',
            '/test-page/test-file'
        ], array_keys($actual));
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
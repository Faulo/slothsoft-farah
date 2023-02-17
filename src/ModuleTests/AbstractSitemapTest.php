<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;
use DOMElement;
use Throwable;

abstract class AbstractSitemapTest extends AbstractTestCase {

    const SCHEMA_URL = 'farah://slothsoft@farah/schema/sitemap/';

    abstract protected static function loadSitesAsset(): AssetInterface;

    protected function getSitesAsset(): AssetInterface {
        static $asset;
        if ($asset === null) {
            $asset = static::loadSitesAsset();
            Kernel::setCurrentSitemap($asset);
        }
        return $asset;
    }

    protected function getSitesResult(): ResultInterface {
        return $this->getSitesAsset()
            ->lookupExecutable()
            ->lookupXmlResult();
    }

    protected function getSitesDocument(): DOMDocument {
        return $this->getSitesResult()
            ->lookupDOMWriter()
            ->toDocument();
    }

    protected function getSitesRoot(): DOMElement {
        return $this->getSitesDocument()->documentElement;
    }

    protected function getSitesIncludes(): array {
        $ret = [];
        $result = $this->getSitesResult();
        $url = $result->createUrl();
        $document = $result->lookupDOMWriter()->toDocument();
        $ret[(string) $url] = $url;
        $this->getSitesIncludesCrawl($ret, $url, $document);
        return $ret;
    }

    protected function getSitesIncludesCrawl(array &$ret, FarahUrl $parentUrl, DOMDocument $document) {
        $nodeList = $document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_INCLUDE_PAGES);
        foreach ($nodeList as $node) {
            $url = FarahUrl::createFromReference($node->getAttribute('ref'), $parentUrl);
            $ret[(string) $url] = $url;
            trigger_error("<include-pages> is deprecated (referencing $url)", E_USER_DEPRECATED);
            try {
                $document = Module::resolveToDOMWriter($url)->toDocument();
                $this->getSitesIncludesCrawl($ret, $url, $document);
            } catch (Throwable $e) {}
        }
    }

    protected function getDomain(): Domain {
        static $domain;
        if ($domain === null) {
            $domain = new Domain($this->getSitesAsset());
        }
        return $domain;
    }

    protected function getDomainDocument(): DOMDocument {
        return $this->getDomain()->getDocument();
    }

    public function testHasRootElement(): DOMElement {
        $rootElement = $this->getSitesDocument()->documentElement;
        $this->assertInstanceOf(DOMElement::class, $rootElement);
        return $rootElement;
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testRootElementIsDomain($rootElement) {
        $this->assertEquals($rootElement->namespaceURI, DOMHelper::NS_FARAH_SITES);
        $this->assertEquals($rootElement->localName, Domain::TAG_DOMAIN);
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testSchemaExists($rootElement): string {
        $version = $rootElement->hasAttribute('version') ? $rootElement->getAttribute('version') : '1.0';
        $path = self::SCHEMA_URL . $version;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }

    /**
     *
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path) {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     *
     * @depends testSchemaIsValidXml
     */
    public function testSitesIsValidAccordingToSchema($schemaDocument) {
        $sitesDocument = $this->getSitesDocument();
        try {
            $validateResult = $sitesDocument->schemaValidate($schemaDocument->documentURI);
        } catch (Throwable $e) {
            $validateResult = false;
            $this->failException($e);
        }
        $this->assertTrue($validateResult, 'Asset file is invalid!');
        return $sitesDocument;
    }

    /**
     *
     * @dataProvider includeProvider
     */
    public function testIncludeExists($url) {
        try {
            $document = Module::resolveToDOMWriter($url)->toDocument();
            $this->assertInstanceOf(DOMElement::class, $document->documentElement);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }

    /**
     *
     * @depends testIncludeExists
     * @dataProvider includeProvider
     */
    public function testIncludeIsValidAccordingToSchema($url) {
        $document = Module::resolveToDOMWriter($url)->toDocument();
        try {
            $schema = $this->testSchemaExists($document->documentElement);
            $validateResult = $document->schemaValidate($schema);
        } catch (Throwable $e) {
            $validateResult = false;
            $this->failException($e);
        }
        $this->assertTrue($validateResult, '<include-pages> document is invalid!');
    }

    public function includeProvider() {
        $ret = [];
        foreach ($this->getSitesIncludes() as $key => $url) {
            $ret[$key] = [
                $url
            ];
        }
        return $ret;
    }

    /**
     *
     * @depends      testIncludeExists
     * @dataProvider pageNodeProvider
     */
    public function testPageMustHaveEitherRefOrRedirect($node) {
        if ($node->hasAttribute('ref')) {
            $this->assertFalse($node->hasAttribute('redirect'), '<page> must not have both ref and redirect attributes.');
            $this->assertNotEmpty($node->getAttribute('ref'), '<page> ref must not be empty.');
            return;
        }
        if ($node->hasAttribute('redirect')) {
            $this->assertFalse($node->hasAttribute('ref'), '<page> must not have both ref and redirect attributes.');
            $this->assertNotEmpty($node->getAttribute('redirect'), '<page> redirect must not be empty.');
            return;
        }
        $this->fail('<page> must have either ref or redirect attribute.');
    }

    /**
     *
     * @depends      testPageMustHaveEitherRefOrRedirect
     * @dataProvider pageNodeProvider
     */
    public function testPageResultExists($node) {
        $path = $node->getAttribute('uri');
        if ($node->hasAttribute('ref')) {
            $this->assertEquals($node, $this->getDomain()
                ->lookupPageNode($path));
            $url = $this->getDomain()->lookupAssetUrl($node);
            $this->assertInstanceOf(ResultInterface::class, Module::resolveToResult($url));
        } else {
            $this->expectException(PageRedirectionException::class);
            $this->getDomain()->lookupPageNode($path);
        }
    }

    public function pageNodeProvider() {
        $ret = [];
        foreach ($this->getDomainDocument()->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_PAGE) as $node) {
            $key = sprintf('%3d: %s', count($ret), $node->getAttribute('uri'));
            $ret[$key] = [
                $node
            ];
        }
        foreach ($this->getDomainDocument()->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_FILE) as $node) {
            $key = sprintf('%3d: %s', count($ret), $node->getAttribute('uri'));
            $ret[$key] = [
                $node
            ];
        }
        return $ret;
    }
}


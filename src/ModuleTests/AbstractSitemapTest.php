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

abstract class AbstractSitemapTest extends AbstractTestCase
{

    const SCHEMA_URL = 'farah://slothsoft@farah/schema/sitemap/1.0';

    abstract protected static function loadSitesAsset(): AssetInterface;

    protected function getSitesAsset(): AssetInterface
    {
        static $asset;
        if ($asset === null) {
            $asset = static::loadSitesAsset();
            Kernel::setCurrentSitemap($asset);
        }
        return $asset;
    }

    protected function getSitesResult(): ResultInterface
    {
        return $this->getSitesAsset()
            ->lookupExecutable()
            ->lookupXmlResult();
    }

    protected function getSitesDocument(): DOMDocument
    {
        return $this->getSitesResult()
            ->lookupDOMWriter()
            ->toDocument();
    }

    protected function getSitesRoot(): DOMElement
    {
        return $this->getSitesDocument()->documentElement;
    }

    protected function getSitesIncludes(): array
    {
        $ret = [];
        $result = $this->getSitesResult();
        $url = $result->createUrl();
        $document = $result->lookupDOMWriter()->toDocument();
        $ret[(string) $url] = $url;
        $this->getSitesIncludesCrawl($ret, $url, $document);
        return $ret;
    }

    protected function getSitesIncludesCrawl(array &$ret, FarahUrl $parentUrl, DOMDocument $document)
    {
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

    protected function getDomain(): Domain
    {
        static $domain;
        if ($domain === null) {
            $domain = new Domain($this->getSitesAsset());
        }
        return $domain;
    }

    protected function getDomainDocument(): DOMDocument
    {
        return $this->getDomain()->getDocument();
    }

    public function testHasRootElement()
    {
        $this->assertInstanceOf(DOMElement::class, $this->getSitesDocument()->documentElement);
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testRootElementIsDomain()
    {
        $this->assertEquals($this->getSitesRoot()->namespaceURI, DOMHelper::NS_FARAH_SITES);
        $this->assertEquals($this->getSitesRoot()->localName, Domain::TAG_DOMAIN);
    }

    public function testSchemaExists()
    {
        $path = self::SCHEMA_URL;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }

    /**
     *
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path)
    {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     *
     * @depends testSchemaIsValidXml
     */
    public function testSitesIsValidAccordingToSchema($schemaDocument)
    {
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
    public function testIncludeExists($url)
    {
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
    public function testIncludeIsValidAccordingToSchema($url)
    {
        $document = Module::resolveToDOMWriter($url)->toDocument();
        try {
            $validateResult = $document->schemaValidate(self::SCHEMA_URL);
        } catch (Throwable $e) {
            $validateResult = false;
            $this->failException($e);
        }
        $this->assertTrue($validateResult, '<include-pages> document is invalid!');
    }

    public function includeProvider()
    {
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
    public function testPageMustHaveEitherRefOrRedirect($node)
    {
        $this->assertNotEquals($node->hasAttribute('ref'), $node->hasAttribute('redirect'), '<page> must have either ref or redirect attribute.');
    }

    /**
     *
     * @depends      testPageMustHaveEitherRefOrRedirect
     * @dataProvider pageNodeProvider
     */
    public function testPageResultExists($node)
    {
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

    public function pageNodeProvider()
    {
        $ret = [];
        foreach ($this->getDomainDocument()->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, 'page') as $node) {
            $key = sprintf('%3d: %s', count($ret), $node->getAttribute('uri'));
            $ret[$key] = [
                $node
            ];
        }
        return $ret;
    }
}

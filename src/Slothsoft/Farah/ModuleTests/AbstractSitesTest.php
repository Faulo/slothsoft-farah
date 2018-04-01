<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;
use DOMElement;
use Throwable;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;

abstract class AbstractSitesTest extends AbstractTestCase
{
    const SCHEMA_URL = 'farah://slothsoft@farah/schema/sites/latest/sites';

    abstract protected static function loadSitesAsset(): AssetInterface;

    protected function getSitesAsset() : AssetInterface
    {
        static $asset;
        if ($asset === null) {
            $asset = static::loadSitesAsset();
        }
        return $asset;
    }
    protected function getSitesResult(): ResultInterface
    {
        return $this->getSitesAsset()->createResult();
    }
    protected function getSitesDocument(): DOMDocument
    {
        return $this->getSitesResult()->toDocument();
    }
    protected function getSitesRoot(): DOMElement
    {
        return $this->getSitesDocument()->documentElement;
    }
    protected function getSitesIncludes() : array {
        $ret = [];
        $result = $this->getSitesResult();
        $this->getSitesIncludesCrawl($ret, $result->getUrl(), $result->toDocument());
        return $ret;
    }
    protected function getSitesIncludesCrawl(array &$ret, FarahUrl $parentUrl, DOMDocument $document) {
        $nodeList = $document->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_INCLUDE_PAGES);
        foreach ($nodeList as $node) {
            $url = FarahUrl::createFromReference(
                $node->getAttribute('ref'),
                $parentUrl->getAuthority(),
                null,
                $parentUrl->getArguments()
            );
            $ret[(string) $url] = $url;
            try {
                $result = FarahUrlResolver::resolveToResult($url);
                $document = $result->toDocument();
                $this->getSitesIncludesCrawl($ret, $url, $document);
            } catch(Throwable $e) {
            }
        }
    }
    protected function getDomain(): Domain
    {
        static $domain;
        if ($domain === null) {
            Kernel::setSitesAsset($this->getSitesAsset());
            $domain = Domain::getInstance();
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
     * @dataProvider includeProvider
     */
    public function testIncludeExists($url) {
        try {
            $result = FarahUrlResolver::resolveToResult($url);
            $this->assertInstanceOf(ResultInterface::class, $result);
        } catch(Throwable $e) {
            $this->failException($e);
        }
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
     * @depends      testIncludeExists
     * @dataProvider pageNodeProvider
     */
    public function testPageMustHaveEitherRefOrRedirect($node) {
        $hasRef = $node->hasAttribute('ref');
        $hasRedirect = $node->hasAttribute('redirect');
        $this->assertTrue($hasRef !== $hasRedirect);
    }
    
    /**
     * @depends      testPageMustHaveEitherRefOrRedirect
     * @dataProvider pageNodeProvider
     */
    public function testPageResultExists($node) {
        if ($node->hasAttribute('ref')) {
            $path = $node->getAttribute('uri');
            $this->assertEquals($node, $this->getDomain()->lookupPageNode($path));
            $url = $this->getDomain()->lookupAssetUrl($node);
            $this->assertInstanceOf(ResultInterface::class, FarahUrlResolver::resolveToResult($url));
        } else {
            $this->assertTrue(true);
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


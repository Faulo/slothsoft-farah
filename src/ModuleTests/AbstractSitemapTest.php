<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use GuzzleHttp\Psr7\Uri;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;
use DOMElement;
use Throwable;

abstract class AbstractSitemapTest extends AbstractTestCase {

    const SCHEMA_URL = 'farah://slothsoft@farah/schema/sitemap/';

    abstract protected static function loadSitesAsset(): AssetInterface;

    private static array $asset = [];

    protected function getSitesAsset(): AssetInterface {
        $id = get_class($this);
        if (! isset(self::$asset[$id])) {
            self::$asset[$id] = static::loadSitesAsset();
            Kernel::setCurrentSitemap(self::$asset[$id]);
        }
        return self::$asset[$id];
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

    protected function getSitesIncludesCrawl(array &$ret, FarahUrl $parentUrl, DOMDocument $document): void {
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

    private static array $domain = [];

    protected function getDomain(): Domain {
        $id = get_class($this);
        if (! isset(self::$domain[$id])) {
            self::$domain[$id] = new Domain($this->getSitesAsset());
        }
        return self::$domain[$id];
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
    public function testRootElementIsDomain(DOMElement $rootElement): void {
        $this->assertEquals($rootElement->namespaceURI, DOMHelper::NS_FARAH_SITES);
        $this->assertEquals($rootElement->localName, Domain::TAG_DOMAIN);
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testSchemaExists(DOMElement $rootElement): string {
        $version = $rootElement->hasAttribute('version') ? $rootElement->getAttribute('version') : '1.0';
        $path = self::SCHEMA_URL . $version;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }

    /**
     *
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path): DOMDocument {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     *
     * @depends testSchemaIsValidXml
     */
    public function testSitesIsValidAccordingToSchema(DOMDocument $schemaDocument): DOMDocument {
        $document = $this->getSitesDocument();
        $this->assertSchema($document, $schemaDocument->documentURI);
        return $document;
    }

    /**
     *
     * @dataProvider includeProvider
     */
    public function testIncludeExists(Farahurl $url): void {
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
    public function testIncludeIsValidAccordingToSchema(Farahurl $url): void {
        $document = Module::resolveToDOMWriter($url)->toDocument();
        $schema = $this->testSchemaExists($document->documentElement);
        $this->assertSchema($document, $schema);
    }

    public function includeProvider(): iterable {
        foreach ($this->getSitesIncludes() as $key => $url) {
            yield $key => [
                $url
            ];
        }
    }

    /**
     *
     * @depends      testIncludeExists
     * @dataProvider pageNodeProvider
     */
    public function testPageMustHaveEitherRefOrRedirect(DOMElement $node): void {
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
    public function testPageResultExists(DOMElement $node): void {
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

    /**
     *
     * @depends      testPageResultExists
     * @dataProvider pageNodeProvider
     */
    public function testPageHasValidLinks(DOMElement $node): void {
        if ($node->hasAttribute('ref')) {
            $url = $this->getDomain()->lookupAssetUrl($node);
            $result = Module::resolveToResult($url);
            $mime = (string) $result->lookupMimeType();

            $this->assertNotEquals('', $mime, sprintf('Asset "%s" failed to provide a mime type.', $url));

            if ($mime === 'application/xml' or substr($mime, - 4) === '+xml') {
                $document = $result->lookupDOMWriter()->toDocument();
                $this->assertNotNull($document->documentElement, sprintf('Asset "%s" is not a valid XML document its mime type.', $url));

                foreach ($document->getElementsByTagNameNS(DOMHelper::NS_HTML, 'a') as $linkNode) {
                    $link = (string) $linkNode->getAttribute('href');
                    $this->assertLink($link, sprintf('Invalid link: <a href="%s">', $link));
                }

                foreach ($document->getElementsByTagNameNS(DOMHelper::NS_HTML, 'img') as $linkNode) {
                    $link = (string) $linkNode->getAttribute('src');
                    $this->assertLink($link, sprintf('Invalid link: <img href="%s">', $link));
                }

                foreach ($document->getElementsByTagNameNS(DOMHelper::NS_XSL, 'include') as $linkNode) {
                    $link = (string) $linkNode->getAttribute('href');
                    $this->assertLink($link, sprintf('Invalid link: <xsl:include href="%s">', $link));
                }
            }
        } else {
            $this->markTestSkipped('Skipping validation because the page does not reference an asset."');
        }
    }

    private function assertLink(string $link, string $message): void {
        $this->assertNotEquals('', $link, $message);

        $uri = new Uri($link);

        if ($uri->getScheme() === 'farah') {
            $this->assertAsset(FarahUrl::createFromUri($uri), $message);
            return;
        }

        if ($uri->getHost()) {
            // external links are assumed to be fine
            return;
        }

        $request = MessageFactory::createCustomRequest('GET', $uri);

        if (preg_match('~^/[^/]+@[^/]+~', $uri->getPath())) {
            $requestStrategy = new LookupAssetStrategy();
        } else {
            $requestStrategy = new LookupPageStrategy();
        }

        try {
            $this->assertAsset($requestStrategy->createUrl($request), $message);
        } catch (HttpStatusException $exception) {
            $this->fail($message . PHP_EOL . sprintf('Resolving link lead to HTTP status "%d"', $uri, $exception->getCode()));
        }
    }

    private function assertAsset(FarahUrl $url, string $message) {
        $this->assertNotNull(Module::resolveToResult($url), $message);
    }

    public function pageNodeProvider(): iterable {
        foreach ($this->getDomainDocument()->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_PAGE) as $node) {
            yield $node->getAttribute('uri') => [
                $node
            ];
        }
        foreach ($this->getDomainDocument()->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_FILE) as $node) {
            yield $node->getAttribute('uri') => [
                $node
            ];
        }
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
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
    
    private const SCHEMA_URL = 'farah://slothsoft@farah/schema/sitemap/';
    
    abstract protected static function loadSitesAsset(): AssetInterface;
    
    protected function getSitesAsset(): AssetInterface {
        $cache = TestCache::instance(get_class($this));
        
        $sitemap = $cache->retrieve('getSitesAsset', function () {
            return static::loadSitesAsset();
        });
        
        Kernel::setCurrentSitemap($sitemap);
        
        return $sitemap;
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
    
    protected function getDomain(): Domain {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('getDomain', function () {
            return Domain::createWithDefaultSitemap();
        });
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
    
    public function includeProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('includeProvider', function () {
            $provider = [];
            foreach ($this->getSitesIncludes() as $key => $url) {
                $provider[$key] ??= [
                    $url
                ];
            }
            return $provider;
        });
    }
    
    /**
     *
     * @depends      testIncludeExists
     * @dataProvider pageNodeProvider
     */
    public function testPageMustHaveOneOfRefOrRedirectOrExt(DOMElement $node): void {
        if ($node->hasAttribute('ref')) {
            $this->assertFalse($node->hasAttribute('redirect'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertFalse($node->hasAttribute('ext'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertNotEmpty($node->getAttribute('ref'), '<page> ref must not be empty.');
            return;
        }
        
        if ($node->hasAttribute('redirect')) {
            $this->assertFalse($node->hasAttribute('ref'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertFalse($node->hasAttribute('ext'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertNotEmpty($node->getAttribute('redirect'), '<page> redirect must not be empty.');
            return;
        }
        
        if ($node->hasAttribute('ext')) {
            $this->assertFalse($node->hasAttribute('ref'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertFalse($node->hasAttribute('redirect'), '<page> must only have one of [ref, redirect, ext].');
            $this->assertNotEmpty($node->getAttribute('ext'), '<page> ext must not be empty.');
            return;
        }
        
        $this->fail('<page> must have either ref or redirect attribute.');
    }
    
    /**
     *
     * @depends      testPageMustHaveOneOfRefOrRedirectOrExt
     * @dataProvider pageNodeProvider
     */
    public function testPageResultExists(DOMElement $node): void {
        $path = $node->getAttribute('uri');
        if ($node->hasAttribute('ref')) {
            $this->assertEquals($node, $this->getDomain()
                ->lookupPageNode($path));
            
            $this->getDomain()->setCurrentPageNode($node);
            $url = $this->getDomain()->lookupAssetUrl($node);
            $this->assertAsset($url);
        } else {
            $this->expectException(PageRedirectionException::class);
            $this->getDomain()->lookupPageNode($path);
        }
    }
    
    private function assertAsset(FarahUrl $url): void {
        try {
            $result = Module::resolveToResult($url);
            $mimeType = $result->lookupMimeType();
        } catch (Throwable $e) {
            $this->failException($e);
            return;
        }
        
        $this->assertNotEquals('', $mimeType, "Asset '$url' did not produce a mime type!");
    }
    
    private function loadAsset(FarahUrl $url): ?ResultInterface {
        try {
            $result = Module::resolveToResult($url);
            $mimeType = $result->lookupMimeType();
            return $mimeType === '' ? null : $result;
        } catch (Throwable $e) {
            return null;
        }
    }
    
    /**
     *
     * @dataProvider pageLinkProvider
     */
    public function testPageHasValidLink(string $context, string $link): void {
        try {
            $this->assertNotEquals('', $link, 'Link must not be empty');
            
            if (strpos($link, 'mailto:') === 0) {
                $this->assertMatchesRegularExpression('~^mailto:.+$~', $link);
                return;
            }
            
            $uri = UriResolver::resolve(new Uri($context), new Uri($link));
            
            if ($uri->getScheme() === 'farah') {
                $this->assertFileExists((string) $uri);
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
            
            $url = $requestStrategy->createUrl($request);
            $result = Module::resolveToResult($url);
            if ($id = $uri->getFragment()) {
                $xpath = DOMHelper::loadXPath($result->lookupDOMWriter()->toDocument());
                $count = (int) $xpath->evaluate(sprintf('count(//*[@id = "%1$s"])', $id));
                $this->assertEquals(1, $count, sprintf('Expected page "%s" to have 1 element with ID "%s"', (string) $uri, $id));
            }
        } catch (HttpStatusException $e) {
            $this->assertLessThan(300, $e->getCode(), sprintf('Resolving link lead to HTTP status "%d":%s%s', $e->getCode(), PHP_EOL, $e->getMessage()));
        }
    }
    
    public function pageNodeProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('pageNodeProvider', function () {
            $provider = [];
            foreach ($this->getDomainDocument()
                ->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_DOMAIN) as $node) {
                $provider[$node->getAttribute('uri')] ??= [
                    $node
                ];
            }
            foreach ($this->getDomainDocument()
                ->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_PAGE) as $node) {
                $provider[$node->getAttribute('uri')] ??= [
                    $node
                ];
            }
            foreach ($this->getDomainDocument()
                ->getElementsByTagNameNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_FILE) as $node) {
                $provider[$node->getAttribute('uri')] ??= [
                    $node
                ];
            }
            return $provider;
        });
    }
    
    private const LINKING_ELEMENTS = [
        [
            DOMHelper::NS_HTML,
            'a',
            'href',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'link',
            'href',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'script',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'img',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'video',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'audio',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'source',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'track',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'iframe',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'embed',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'form',
            'action',
            false
        ],
        [
            DOMHelper::NS_XSL,
            'include',
            'href',
            true
        ],
        [
            DOMHelper::NS_XSL,
            'import',
            'href',
            true
        ],
        [
            DOMHelper::NS_XSD,
            'include',
            'schemaLocation',
            true
        ],
        [
            DOMHelper::NS_XSD,
            'import',
            'schemaLocation',
            false
        ],
        [
            DOMHelper::NS_XINCLUDE,
            'include',
            'href',
            true
        ]
    ];
    
    public function pageLinkProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('pageLinkProvider', function () {
            $provider = [];
            $pages = $this->pageNodeProvider();
            foreach ($pages as $page => $args) {
                $node = $args[0];
                
                if ($node->hasAttribute('ref')) {
                    $this->getDomain()
                        ->setCurrentPageNode($node);
                    
                    $url = $this->getDomain()
                        ->lookupAssetUrl($node);
                    
                    if (file_exists((string) $url) and $result = $this->loadAsset($url)) {
                        $mime = $result->lookupMimeType();
                        
                        if ($mime === 'application/xml' or substr($mime, - 4) === '+xml') {
                            $document = $result->lookupDOMWriter()
                                ->toDocument();
                            
                            foreach (self::LINKING_ELEMENTS as $args) {
                                [
                                    $ns,
                                    $tag,
                                    $attribute,
                                    $isRequired
                                ] = $args;
                                foreach ($document->getElementsByTagNameNS($ns, $tag) as $linkNode) {
                                    $link = (string) $linkNode->getAttribute($attribute);
                                    
                                    if ($link === '') {
                                        // use fallback attribute
                                        $link = (string) $linkNode->getAttribute('data-' . $attribute);
                                    }
                                    
                                    if (isset($pages[$link])) {
                                        // page already asserted by pageNodeProvider
                                        continue;
                                    }
                                    
                                    if ($link === '' and ! $isRequired) {
                                        continue;
                                    }
                                    
                                    $reference = implode(' ', [
                                        $page,
                                        $tag,
                                        $attribute,
                                        "'$link'"
                                    ]);
                                    $provider[$reference] ??= [
                                        $page,
                                        $link
                                    ];
                                }
                            }
                        }
                    }
                    
                    $this->getDomain()
                        ->clearCurrentPageNode();
                }
            }
            return $provider;
        });
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Ds\Set;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use DOMDocument;
use DOMElement;
use Throwable;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\MimeTypeDictionary;

abstract class AbstractModuleTest extends AbstractTestCase {
    
    abstract protected static function getManifestAuthority(): FarahUrlAuthority;
    
    protected function getManifest(): ManifestInterface {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('getManifest', function () {
            return Module::resolveToManifest($this->getManifestUrl());
        });
    }
    
    protected function getManifestUrl(): FarahUrl {
        return FarahUrl::createFromComponents(static::getManifestAuthority());
    }
    
    protected function getManifestAsset(): AssetInterface {
        return $this->getManifest()->lookupAsset('/');
    }
    
    protected function getManifestDocument(): DOMDocument {
        return $this->getManifestMethod('getRootElement')->toDocument();
    }
    
    protected function getManifestProperty(string $name) {
        return $this->getObjectProperty($this->getManifest(), $name);
    }
    
    protected function getManifestMethod(string $name) {
        return $this->getObjectMethod($this->getManifest(), $name);
    }
    
    /**
     *
     * @dataProvider assetReferenceProvider
     */
    public function testAssetReferenceIsValid(string $ref, FarahUrl $context): void {
        try {
            FarahUrl::createFromReference($ref, $context);
            $this->assertTrue(true);
        } catch (MalformedUrlException $e) {
            $this->failException($e);
        } catch (ProtocolNotSupportedException $e) {
            $this->failException($e);
        }
    }
    
    public function assetReferenceProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('assetReferenceProvider', function () {
            $provider = [];
            foreach ($this->getReferencedAssetReferences() as $context => $path) {
                $key = (string) $path;
                $provider[$key] ??= [
                    $path,
                    $context
                ];
            }
            return $provider;
        });
    }
    
    public function assetReferenceUrlProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('assetReferenceUrlProvider', function () {
            $provider = [];
            foreach ($this->getReferencedAssetReferences() as $context => $ref) {
                try {
                    $url = FarahUrl::createFromReference($ref, $context);
                    $key = (string) $url;
                    $provider[$key] ??= [
                        $url
                    ];
                } catch (Throwable $e) {}
            }
            return $provider;
        });
    }
    
    private function getReferencedAssetReferences(): iterable {
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            if ($node->hasAttribute('ref')) {
                yield $this->getContextUrlForManifestNode($node) => $node->getAttribute('ref');
            }
        }
    }
    
    private function getContextUrlForManifestNode(DOMElement $node): FarahUrl {
        $path = [];
        while ($node->parentNode instanceof DOMElement) {
            if ($node->hasAttribute('name')) {
                $path[] = $node->getAttribute('name');
            } else {
                $path[] = '*';
            }
            $node = $node->parentNode;
        }
        $path = array_reverse($path);
        $path = FarahUrlPath::createFromSegments($path);
        return $this->getManifestUrl()->withAssetPath($path);
    }
    
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedModuleExists(FarahUrl $url): void {
        try {
            Module::resolveToManifest($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetExists(FarahUrl $url): void {
        try {
            Module::resolveToAsset($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedExecutableExists(FarahUrl $url): void {
        try {
            Module::resolveToExecutable($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedResultExists(FarahUrl $url): void {
        try {
            Module::resolveToResult($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    
    public function assetLocalUrlProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('assetLocalUrlProvider', function () {
            $provider = [];
            foreach ($this->getLocalAssetPaths() as $path) {
                try {
                    $url = $this->getManifest()
                        ->createUrl($path);
                    $key = (string) $url;
                    $provider[$key] ??= [
                        $url
                    ];
                } catch (Throwable $e) {}
            }
            return $provider;
        });
    }
    
    private function getLocalAssetPaths(): iterable {
        yield from $this->buildPathIndex($this->getManifestAsset());
    }
    
    private function buildPathIndex(AssetInterface $asset): iterable {
        if ($asset->isImportSelfInstruction()) {
            return;
        }
        if ($asset->isImportChildrenInstruction()) {
            return;
        }
        if ($asset->createUrl()->getAssetAuthority() !== static::getManifestAuthority()) {
            return;
        }
        
        yield $asset->getUrlPath();
        foreach ($asset->getAssetChildren() as $childAsset) {
            yield from $this->buildPathIndex($childAsset);
        }
    }
    
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalAssetExists(FarahUrl $url): void {
        try {
            $asset = Module::resolveToAsset($url);
            $this->assertNotNull($asset);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalExecutableExists(FarahUrl $url): void {
        try {
            Module::resolveToExecutable($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalResultExists(FarahUrl $url): void {
        try {
            Module::resolveToResult($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    
    /**
     *
     * @depends      testLocalResultExists
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalResultIsValidAccordingToSchema(FarahUrl $url): void {
        $asset = Module::resolveToAsset($url);
        $executable = $asset->lookupExecutable();
        $result = $executable->lookupDefaultResult();
        $mimeType = $result->lookupMimeType();
        
        if (! MimeTypeDictionary::isXml($mimeType)) {
            $this->markTestSkipped("Won't attempt to validate non-XML resource '$mimeType'");
            return;
        }
        
        $document = $result->lookupDOMWriter()->toDocument();
        
        if ($schema = $this->findSchemaLocation($document)) {
            $this->assertSchema($document, $schema);
        }
    }
    
    /**
     *
     * @dataProvider assetLinkProvider
     */
    public function testAssetHasValidLink(string $context, string $link): void {
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
    
    public function assetLinkProvider(): array {
        $cache = TestCache::instance(get_class($this));
        
        return $cache->retrieve('assetLinkProvider', function () {
            $provider = [];
            $localUrls = $this->assetLocalUrlProvider();
            $crawler = new LinkCrawler(new Set(array_keys($localUrls)));
            foreach ($localUrls as $asset => $args) {
                $url = $args[0];
                
                if (file_exists($asset) and $result = Module::resolveToResult($url)) {
                    $mime = $result->lookupMimeType();
                    
                    if ($mime === 'application/xml' or substr($mime, - 4) === '+xml') {
                        $document = $result->lookupDOMWriter()
                            ->toDocument();
                        
                        foreach ($crawler->crawlDocument($document) as $reference => $link) {
                            $provider["$asset $reference"] ??= [
                                $asset,
                                $link
                            ];
                        }
                    }
                }
            }
            return $provider;
        });
    }
}


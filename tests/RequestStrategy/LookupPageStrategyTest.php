<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use DOMDocument;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Sites\Domain;
use Slothsoft\FarahTesting\TestUtils;

/**
 * LookupPageStrategyTest
 *
 * @see LookupPageStrategy
 */
final class LookupPageStrategyTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(LookupPageStrategy::class), "Failed to load class 'Slothsoft\Farah\RequestStrategy\LookupPageStrategy'!");
    }
    
    private const SITEMAP = 'test-files/domain.xml';
    
    private const ITERATIONS = 1_000;
    
    /**
     *
     * @dataProvider urlProvider
     * @throws Exception
     */
    public function test_createUrl(string $path, string $reference): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $domain = new Domain($document);
        
        $_SERVER['REQUEST_URI'] = $path;
        
        $requestStrategy = new LookupPageStrategy($domain);
        
        $request = MessageFactory::createServerRequest();
        
        $expected = FarahUrl::createFromReference($reference);
        
        try {
            $actual = $requestStrategy->createUrl($request);
        } catch (HttpStatusException $e) {
            $headers = $e->getAdditionalHeaders();
            $this->assertThat($headers, new ArrayHasKey('location'), "Expected a redirect to '$reference', but got: $e");
            $_SERVER['REQUEST_URI'] = $headers['location'];
            $request = MessageFactory::createServerRequest();
            $actual = $requestStrategy->createUrl($request);
        }
        
        $this->assertThat($actual, new IsEqual($expected));
        
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $requestStrategy->createUrl($request);
        }
    }
    
    public function urlProvider(): iterable {
        yield 'domain' => [
            '/',
            'farah://slothsoft@schema.slothsoft.net/pages/index'
        ];
        
        yield 'page' => [
            '/schema/historical-games-night/2.0/',
            'farah://slothsoft@schema.slothsoft.net/pages/schema/documentation?schema=farah://slothsoft@schema/schema/historical-games-night&version=2.0'
        ];
        
        yield 'file' => [
            '/schema/versioning/1.0.xsd',
            'farah://slothsoft@schema/schema/versioning/1.0?schema=farah://slothsoft@schema/schema/versioning'
        ];
        
        yield 'redirect-root' => [
            '/sitemap/redirect-root',
            'farah://slothsoft@schema.slothsoft.net/pages/index'
        ];
        
        yield 'redirect-up' => [
            '/sitemap/redirect-up',
            'farah://slothsoft@farah/sitemap-generator'
        ];
        
        yield 'redirect-versioning' => [
            '/sitemap/redirect-versioning',
            'farah://slothsoft@schema.slothsoft.net/pages/schema/home?schema=farah://slothsoft@schema/schema/versioning'
        ];
        
        yield 'case mismatch' => [
            '/Schema/historical-Games-night/',
            'farah://slothsoft@schema.slothsoft.net/pages/schema/home?schema=farah://slothsoft@schema/schema/historical-games-night'
        ];
    }

    /**
     * @throws Exception
     */
    public function test_createUrl_appliesConfiguredDefaultStream(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $domain = new Domain($document);

        $_SERVER['REQUEST_URI'] = '/';

        $requestStrategy = new LookupPageStrategy($domain, Executable::resultIsHtml());
        $actual = $requestStrategy->createUrl(MessageFactory::createServerRequest());

        $this->assertThat($actual, new IsEqual(FarahUrl::createFromReference('farah://slothsoft@schema.slothsoft.net/pages/index#html')));
    }

    /**
     * @throws Exception
     */
    public function test_createUrl_preservesExplicitStream(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $document->documentElement->setAttribute(Domain::ATTR_REFERENCE, 'pages/index#xml');
        $domain = new Domain($document);

        $_SERVER['REQUEST_URI'] = '/';

        $requestStrategy = new LookupPageStrategy($domain, Executable::resultIsHtml());
        $actual = $requestStrategy->createUrl(MessageFactory::createServerRequest());

        $this->assertThat($actual, new IsEqual(FarahUrl::createFromReference('farah://slothsoft@schema.slothsoft.net/pages/index#xml')));
    }

    /**
     *
     * @runInSeparateProcess
     * @throws Exception
     */
    public function test_process_appliesHTMLOnlyToPageLookup(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');

        $document = new DOMDocument();
        /** @noinspection HttpUrlsUsage */
        $document->loadXML(<<<'XML'
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="slothsoft" module="test-module" ref="/tests/linking" uri="/" version="1.1" />
XML
        );
        $domain = new Domain($document);

        $assetUrl = FarahUrl::createFromReference('farah://slothsoft@test-module/tests/linking');
        $this->assertSame('application/xhtml+xml', Module::resolveToResult($assetUrl)->lookupMimeType());

        $_SERVER['REQUEST_URI'] = '/';
        $requestStrategy = new LookupPageStrategy($domain, Executable::resultIsHtml());
        $response = $requestStrategy->process(MessageFactory::createServerRequest());

        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine('content-type'));
        $this->assertStringContainsString('filename="transformation.html"', $response->getHeaderLine('content-disposition'));
        $this->assertStringStartsWith('<!DOCTYPE html>', (string) $response->getBody());
    }

    /**
     * @dataProvider physicalHtmlPageProvider
     * @runInSeparateProcess
     * @throws Exception
     */
    public function test_process_honorsStreamForPhysicalHtmlPage(string $assetName, string $stream, string $expectedMimeType): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');

        $document = new DOMDocument();
        /** @noinspection HttpUrlsUsage */
        $document->loadXML(<<<XML
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="slothsoft" module="test-module" ref="/$assetName" uri="/" version="1.1" />
XML
        );
        $requestStrategy = new LookupPageStrategy(new Domain($document), FarahUrlStreamIdentifier::createFromString($stream));
        $response = $requestStrategy->process(new ServerRequest('GET', 'http://localhost/'));
        $body = (string) $response->getBody();

        $this->assertSame("$expectedMimeType; charset=UTF-8", $response->getHeaderLine('content-type'));
        if ($stream === Executable::RESULT_IS_HTML) {
            $this->assertStringStartsWith('<!DOCTYPE html>', $body);
            $this->assertStringNotContainsString('<?xml', $body);
            $this->assertStringContainsString('<br>', $body);
            $this->assertStringNotContainsString('<br />', $body);
            $this->assertStringContainsString('<script type="module"></script>', $body);
        } else {
            $this->assertStringStartsWith('<?xml', $body);
            $this->assertStringContainsString('xmlns="http://www.w3.org/1999/xhtml"', $body);
            $this->assertMatchesRegularExpression('~<br\s*/>~', $body);
        }
    }

    public function physicalHtmlPageProvider(): iterable {
        yield '.html + #html' => [
            'page-html',
            Executable::RESULT_IS_HTML,
            'text/html'
        ];
        yield '.html + #xml' => [
            'page-html',
            Executable::RESULT_IS_XML,
            'application/xhtml+xml'
        ];
        yield '.xhtml + #html' => [
            'page-xhtml',
            Executable::RESULT_IS_HTML,
            'text/html'
        ];
        yield '.xhtml + #xml' => [
            'page-xhtml',
            Executable::RESULT_IS_XML,
            'application/xhtml+xml'
        ];
    }

    /**
     * @runInSeparateProcess
     * @throws Exception
     */
    public function test_process_preservesExplicitStreamForPhysicalHtmlPage(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');

        $document = new DOMDocument();
        /** @noinspection HttpUrlsUsage */
        $document->loadXML(<<<'XML'
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="slothsoft" module="test-module" ref="/page-xhtml#xml" uri="/" version="1.1" />
XML
        );
        $response = (new LookupPageStrategy(new Domain($document), Executable::resultIsHtml()))->process(new ServerRequest('GET', 'http://localhost/'));
        $body = (string) $response->getBody();

        $this->assertSame('application/xhtml+xml; charset=UTF-8', $response->getHeaderLine('content-type'));
        $this->assertStringStartsWith('<?xml', $body);
        $this->assertStringContainsString('xmlns="http://www.w3.org/1999/xhtml"', $body);
    }
    
    /**
     *
     * @dataProvider webSchemeProvider
     * @runInSeparateProcess
     * @throws Exception
     */
    public function test_process_acceptsWebScheme(string $scheme): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
        
        $document = new DOMDocument();
        /** @noinspection HttpUrlsUsage */
        $document->loadXML(<<<'XML'
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="slothsoft" module="test-module" ref="/tests/linking" uri="/" version="1.1" />
XML
        );
        $request = new ServerRequest('GET', "$scheme://localhost/");
        $response = (new LookupPageStrategy(new Domain($document)))->process($request);
        
        $this->assertSame(200, $response->getStatusCode());
    }
    
    public function webSchemeProvider(): iterable {
        yield 'HTTP' => [
            'http'
        ];
        yield 'HTTPS' => [
            'https'
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     * @throws Exception
     */
    public function test_process_rejectsUnsupportedScheme(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = new DOMDocument();
        /** @noinspection HttpUrlsUsage */
        $document->loadXML(<<<'XML'
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="slothsoft" module="test-module" ref="/tests/linking" uri="/" version="1.1" />
XML
        );
        $request = new ServerRequest('GET', 'ftp://localhost/');
        $response = (new LookupPageStrategy(new Domain($document)))->process($request);
        
        $this->assertSame(501, $response->getStatusCode());
    }
    
    /**
     *
     * @dataProvider redirectProvider
     * @throws Exception
     */
    public function test_createUrl_redirects(string $path, string $redirect): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $domain = new Domain($document);
        
        $_SERVER['REQUEST_URI'] = $path;
        
        $requestStrategy = new LookupPageStrategy($domain);
        
        $request = MessageFactory::createServerRequest();
        
        try {
            $requestStrategy->createUrl($request);
            $this->fail("Expected redirect to '$redirect'.");
        } catch (HttpStatusException $e) {
            $headers = $e->getAdditionalHeaders();
            $this->assertThat($headers, new ArrayHasKey('location'), "Expected a redirect to '$redirect', but got: $e");
            $this->assertThat($headers['location'], new IsEqual($redirect));
        }
    }
    
    public function redirectProvider(): iterable {
        yield 'redirect-root' => [
            '/sitemap/redirect-root',
            '/'
        ];
        
        yield 'redirect-up' => [
            '/sitemap/redirect-up',
            '/sitemap/'
        ];
        
        yield 'redirect-versioning' => [
            '/sitemap/redirect-versioning',
            '/schema/versioning/'
        ];
        
        yield 'case mismatch' => [
            '/Schema/historical-Games-night/',
            '/schema/historical-games-night/'
        ];
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Sites\Domain;

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
     */
    public function test_createUrl(string $path, string $reference): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $domain = new Domain($document);
        
        $_SERVER['REQUEST_URI'] = $path;
        
        $requestStrategy = new LookupPageStrategy($domain);
        
        $request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        
        $expected = FarahUrl::createFromReference($reference);
        
        try {
            $actual = $requestStrategy->createUrl($request);
        } catch (HttpStatusException $e) {
            $headers = $e->getAdditionalHeaders();
            $this->assertThat($headers, new ArrayHasKey('location'), "Expected a redirect to '$reference', but got: $e");
            $_SERVER['REQUEST_URI'] = $headers['location'];
            $request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
            $actual = $requestStrategy->createUrl($request);
        }
        
        $this->assertThat($actual, new IsEqual($expected));
        
        for ($i = 0; $i < self::ITERATIONS; $i ++) {
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
     *
     * @dataProvider redirectProvider
     */
    public function test_createUrl_redirects(string $path, string $redirect): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        $document = DOMHelper::loadDocument(self::SITEMAP);
        $domain = new Domain($document);
        
        $_SERVER['REQUEST_URI'] = $path;
        
        $requestStrategy = new LookupPageStrategy($domain);
        
        $request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        
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
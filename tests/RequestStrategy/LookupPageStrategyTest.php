<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\TestUtils;
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
    
    private const ITERATIONS = 10_000;
    
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
        
        $actual = $requestStrategy->createUrl($request);
        
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
    }
}
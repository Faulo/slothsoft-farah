<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\MessageFactory;

/**
 * LookupAssetStrategyTest
 *
 * @see LookupAssetStrategy
 */
final class LookupAssetStrategyTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(LookupAssetStrategy::class), "Failed to load class 'Slothsoft\Farah\RequestStrategy\LookupAssetStrategy'!");
    }
    
    /**
     *
     * @dataProvider assetUrlProvider
     */
    public function testLookupUrl(UriInterface $input, FarahUrl $expected): void {
        $request = MessageFactory::createCustomRequest('GET', $input);
        
        $sut = new LookupAssetStrategy();
        $actual = $sut->createUrl($request);
        
        $this->assertEquals($expected, $actual);
    }
    
    public function assetUrlProvider(): iterable {
        yield 'path' => [
            new Uri('/slothsoft@farah/phpinfo'),
            FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo')
        ];
        
        yield 'path with query' => [
            new Uri('/slothsoft@farah/phpinfo?a=b'),
            FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo?a=b')
        ];
        
        yield 'farah url' => [
            FarahUrl::createFromUri(new Uri('farah://slothsoft@farah/phpinfo?a=b')),
            FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo?a=b')
        ];
    }
}
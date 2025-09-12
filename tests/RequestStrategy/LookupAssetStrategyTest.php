<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\MessageFactory;
use GuzzleHttp\Psr7\Uri;
use Laminas\Uri\UriInterface;

/**
 * LookupAssetStrategyTest
 *
 * @see LookupAssetStrategy
 */
class LookupAssetStrategyTest extends TestCase {

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
    }
}
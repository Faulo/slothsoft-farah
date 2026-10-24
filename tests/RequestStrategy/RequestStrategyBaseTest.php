<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\StreamingResponse;
use Slothsoft\Farah\Module\Module;

/**
 * RequestStrategyBaseTest
 *
 * @see RequestStrategyBase
 *
 */
final class RequestStrategyBaseTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(RequestStrategyBase::class), "Failed to load class 'Slothsoft\Farah\RequestStrategy\RequestStrategyBase'!");
    }
    
    /**
     * @dataProvider bufferabilityProvider
     * @runInSeparateProcess
     */
    public function testProcessPreservesBufferabilityWithoutChangingOutputBuffers(bool $bufferable, string $method): void {
        Module::registerWithXmlManifestAndDefaultAssets('test@streaming', dirname(__DIR__, 2) . '/test-files/http-streaming');
        $_GET = $bufferable ? ['buffered' => '1'] : [];
        ob_start();
        try {
            echo 'existing content';
            $level = ob_get_level();
            $url = FarahUrl::createFromReference('farah://test@streaming/events');
            $result = Module::resolveToResult($url);
            $this->assertSame($bufferable, $result->lookupIsBufferable());
            $response = (new LookupAssetStrategy())->process(new ServerRequest($method, (string) $url, ['Accept-Encoding' => 'gzip']));
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame(! $bufferable, $response instanceof StreamingResponse);
            $this->assertSame($bufferable ? '' : 'no', $response->getHeaderLine('X-Accel-Buffering'));
            $this->assertSame($level, ob_get_level());
            $this->assertSame('existing content', ob_get_contents());
            if (! $bufferable) {
                $this->assertFalse($response->hasHeader('Content-Encoding'));
                $this->assertFalse($response->hasHeader('Content-Length'));
            }
            if ($method === 'HEAD') {
                $this->assertSame('', (string) $response->getBody());
            } elseif (! $bufferable) {
                $this->assertSame("event:start\ndata:\n\n", $response->getBody()->read(1024));
            }
        } finally {
            ob_end_clean();
        }
    }
    
    public function bufferabilityProvider(): iterable {
        yield 'streaming GET' => [false, 'GET'];
        yield 'streaming HEAD' => [false, 'HEAD'];
        yield 'buffered GET' => [true, 'GET'];
    }
}

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\RequestStrategy\RequestStrategyInterface;
use Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface;

/**
 * KernelTest
 *
 * @see Kernel
 */
final class KernelTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Kernel::class), "Failed to load class 'Slothsoft\Farah\Kernel'!");
    }

    /**
     *
     * @test
     * @runInSeparateProcess
     */
    public function testHandleReturnsInternalServerErrorWhenRequestProcessingThrows(): void {
        $request = new ServerRequest('GET', 'https://example.com/');
        $exception = new RuntimeException('Page building failed.');

        $requestStrategy = $this->createMock(RequestStrategyInterface::class);
        $requestStrategy->expects($this->once())
            ->method('process')
            ->with($request)
            ->willThrowException($exception);

        $responseStrategy = $this->createMock(ResponseStrategyInterface::class);
        $responseStrategy->expects($this->once())
            ->method('process')
            ->with($this->callback(function (ResponseInterface $response) use ($exception): bool {
                $body = (string) $response->getBody();
                $this->assertSame(StatusCode::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
                $this->assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('content-type'));
                $this->assertStringContainsString((string) $exception, $body);
                return true;
            }));

        $response = Kernel::getInstance()->handle($requestStrategy, $responseStrategy, $request);

        $this->assertSame(StatusCode::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Page building failed.', (string) $response->getBody());
    }

    /**
     *
     * @test
     * @runInSeparateProcess
     */
    public function testHandlePreservesExistingResponse(): void {
        $request = new ServerRequest('GET', 'https://example.com/');
        $expectedResponse = new Response(StatusCode::STATUS_NOT_FOUND);

        $requestStrategy = $this->createMock(RequestStrategyInterface::class);
        $requestStrategy->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn($expectedResponse);

        $responseStrategy = $this->createMock(ResponseStrategyInterface::class);
        $responseStrategy->expects($this->once())
            ->method('process')
            ->with($expectedResponse);

        $response = Kernel::getInstance()->handle($requestStrategy, $responseStrategy, $request);

        $this->assertSame($expectedResponse, $response);
    }
}

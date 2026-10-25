<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/** Documents the payload and response disposition carried by HttpDownloadExecutableException. */
final class HttpDownloadExecutableExceptionTest extends TestCase {
    /** @dataProvider dispositionProvider */
    public function testPreservesPayloadAndDisposition(?bool $inline): void {
        $payload = new ResultStrategies($this->createMock(StreamBuilderStrategyInterface::class));
        $exception = $inline === null ? new HttpDownloadExecutableException($payload) : new HttpDownloadExecutableException($payload, $inline);
        $this->assertSame($payload, $exception->getExecutable());
        $this->assertSame($inline ?? false, $exception->isInline());
    }
    
    public function dispositionProvider(): iterable {
        yield 'attachment by default' => [null];
        yield 'explicit attachment' => [false];
        yield 'inline' => [true];
    }
}
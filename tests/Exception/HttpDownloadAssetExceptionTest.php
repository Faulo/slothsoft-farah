<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;

/** Documents the payload and response disposition carried by HttpDownloadAssetException. */
final class HttpDownloadAssetExceptionTest extends TestCase {
    /** @dataProvider dispositionProvider */
    public function testPreservesPayloadAndDisposition(?bool $inline): void {
        $payload = new ExecutableStrategies($this->createMock(ResultBuilderStrategyInterface::class));
        $exception = $inline === null ? new HttpDownloadAssetException($payload) : new HttpDownloadAssetException($payload, $inline);
        $this->assertSame($payload, $exception->getStrategies());
        $this->assertSame($inline ?? false, $exception->isInline());
    }
    
    public function dispositionProvider(): iterable {
        yield 'attachment by default' => [null];
        yield 'explicit attachment' => [false];
        yield 'inline' => [true];
    }
}
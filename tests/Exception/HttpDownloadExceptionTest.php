<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Module\Result\ResultInterface;

/** Documents the payload and response disposition carried by HttpDownloadException. */
final class HttpDownloadExceptionTest extends TestCase {
    /** @dataProvider dispositionProvider */
    public function testPreservesPayloadAndDisposition(?bool $inline): void {
        $payload = $this->createMock(ResultInterface::class);
        $exception = $inline === null ? new HttpDownloadException($payload) : new HttpDownloadException($payload, $inline);
        $this->assertSame($payload, $exception->getResult());
        $this->assertSame($inline ?? false, $exception->isInline());
    }
    
    public function dispositionProvider(): iterable {
        yield 'attachment by default' => [null];
        yield 'explicit attachment' => [false];
        yield 'inline' => [true];
    }
}
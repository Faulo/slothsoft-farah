<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Farah\Exception\HttpDownloadAssetException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpDownloadExecutableException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

final class ExecutableDownloadTest extends TestCase {
    /**
     * @dataProvider downloadProvider
     * @runInSeparateProcess
     */
    public function testResultBuilderCanForceAResponse(string $stage, string $stream, bool $inline): void {
        $type = FarahUrlStreamIdentifier::createFromString($stream);
        $streamBuilder = $this->createMock(StreamBuilderStrategyInterface::class);
        $streamBuilder->method('buildStreamFileName')->willReturn('selected-file.bin');
        $resultStrategies = new ResultStrategies($streamBuilder);
        $replacement = $this->createMock(ResultBuilderStrategyInterface::class);
        $original = $this->createMock(ResultBuilderStrategyInterface::class);
        $original->method('isDifferentFromDefault')->willReturn(true);
        $executable = new Executable($this->createMock(AssetInterface::class), FarahUrlArguments::createFromValueList([]), new ExecutableStrategies($original));
        $directResult = $this->createMock(ResultInterface::class);
        $directResult->method('lookupFileName')->willReturn('selected-file.bin');
        switch ($stage) {
            case 'asset':
                $exception = new HttpDownloadAssetException(new ExecutableStrategies($replacement), $inline);
                $replacement->expects(self::once())->method('buildResultStrategies')->with(self::identicalTo($executable), self::identicalTo($type))->willReturn($resultStrategies);
                break;
            case 'result':
                $exception = new HttpDownloadExecutableException($resultStrategies, $inline);
                $replacement->expects(self::never())->method('buildResultStrategies');
                break;
            default:
                $exception = new HttpDownloadException($directResult, $inline);
                $replacement->expects(self::never())->method('buildResultStrategies');
                break;
        }
        $original->expects(self::once())->method('buildResultStrategies')->with(self::identicalTo($executable), self::identicalTo($type))->willThrowException($exception);
        try {
            $executable->lookupResult($type);
            self::fail('The builder must interrupt normal result lookup.');
        } catch (HttpDownloadException $e) {
            $this->assertSame($inline, $e->isInline());
            $this->assertSame('selected-file.bin', $e->getResult()->lookupFileName());
            if ($stage === 'direct') {
                $this->assertSame($exception, $e);
                $this->assertSame($directResult, $e->getResult());
            }
        }
    }
    
    public function downloadProvider(): iterable {
        foreach (['asset', 'result', 'direct'] as $stage) {
            foreach (['', 'xml'] as $stream) {
                foreach ([false, true] as $inline) {
                    yield "$stage / $stream / " . ($inline ? 'inline' : 'attachment') => [$stage, $stream, $inline];
                }
            }
        }
    }
    
    /** @runInSeparateProcess */
    public function testUnrelatedFailuresAreNotConvertedToDownloadResponses(): void {
        $failure = new RuntimeException('Unable to build the result.');
        $builder = $this->createMock(ResultBuilderStrategyInterface::class);
        $builder->method('buildResultStrategies')->willThrowException($failure);
        $executable = new Executable($this->createMock(AssetInterface::class), FarahUrlArguments::createFromValueList([]), new ExecutableStrategies($builder));
        try {
            $executable->lookupDefaultResult();
            self::fail('Expected the original failure.');
        } catch (RuntimeException $e) {
            $this->assertSame($failure, $e);
        }
    }
}

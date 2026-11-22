<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/** @see Result */
final class ResultTest extends TestCase {
    
    /** @param array{mtime?: int, size?: int, ...<array-key, int>} $statistics */
    private function createResultWithStatistics(array $statistics): Result {
        $builder = $this->createMock(StreamBuilderStrategyInterface::class);
        $builder->method('buildStreamFileStatistics')->willReturn($statistics);
        return new Result($this->createMock(ExecutableInterface::class), FarahUrlStreamIdentifier::createEmpty(), new ResultStrategies($builder));
    }
    
    /** @return iterable<string, array{array{size?: int}, int}> */
    public static function fileSizes(): iterable {
        yield 'unknown' => [[], 0];
        yield 'empty file' => [['size' => 0], 0];
        yield 'native integer' => [['size' => 42], 42];
        yield 'negative size' => [['size' => -1], 0];
        yield 'integer limit' => [['size' => PHP_INT_MAX], PHP_INT_MAX];
    }
    
    /**
     * @dataProvider fileSizes
     * @param array{size?: int} $statistics
     */
    public function testFileSizeFitsInteger(array $statistics, int $expected): void {
        $this->assertSame($expected, $this->createResultWithStatistics($statistics)->lookupFileSize());
    }
    
    public function testAdditionalFileStatisticsArePreserved(): void {
        $statistics = ['size' => 42, 'mtime' => 123, 'mode' => 0100644];
        $result = $this->createResultWithStatistics($statistics);
        $this->assertSame($statistics, $result->lookupFileStatistics());
        $this->assertSame(123, $result->lookupFileChangeTime());
    }
}

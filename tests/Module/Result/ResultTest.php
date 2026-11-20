<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/** @see Result */
final class ResultTest extends TestCase {
    
    /** @param array{mtime?: int, size?: int|string, ...<array-key, int>} $statistics */
    private function createResultWithStatistics(array $statistics): Result {
        $builder = $this->createMock(StreamBuilderStrategyInterface::class);
        $builder->method('buildStreamFileStatistics')->willReturn($statistics);
        return new Result($this->createMock(ExecutableInterface::class), FarahUrlStreamIdentifier::createEmpty(), new ResultStrategies($builder));
    }
    
    /** @return iterable<string, array{array{size?: int|string}, int}> */
    public static function fileSizes(): iterable {
        yield 'unknown' => [[], 0];
        yield 'empty file' => [['size' => 0], 0];
        yield 'native integer' => [['size' => 42], 42];
        yield 'decimal string' => [['size' => '42'], 42];
        yield 'leading zeros' => [['size' => '00042'], 42];
        yield 'integer limit' => [['size' => (string) PHP_INT_MAX], PHP_INT_MAX];
    }
    
    /**
     * @dataProvider fileSizes
     * @param array{size?: int|string} $statistics
     */
    public function testFileSizeFitsInteger(array $statistics, int $expected): void {
        $this->assertSame($expected, $this->createResultWithStatistics($statistics)->lookupFileSize());
    }
    
    public function testVirtualSizeOutsideNativeRangeIsStillAvailableInStatistics(): void {
        $size = (string) PHP_INT_MAX . '0';
        $statistics = ['size' => $size, 'mtime' => 123, 'mode' => 0100644];
        $result = $this->createResultWithStatistics($statistics);
        $this->assertSame(0, $result->lookupFileSize());
        $this->assertSame($statistics, $result->lookupFileStatistics());
        $this->assertSame(123, $result->lookupFileChangeTime());
    }
}

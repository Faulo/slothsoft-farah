<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;
use OverflowException;
use TypeError;
use UnderflowException;

use PHPUnit\Framework\TestCase;

final class ByteCountTest extends TestCase {
    /** @dataProvider arithmeticProvider */
    public function testExactArithmetic(string $left, string $right, string $sum): void {
        $a = new ByteCount($left);
        $b = new ByteCount($right);
        $this->assertSame($sum, (string) $a->add($b));
        $this->assertSame((string) $a, (string) (new ByteCount($sum))->subtract($b));
        $this->assertSame((string) $b, (string) (new ByteCount($sum))->subtract($a));
    }
    
    public function arithmeticProvider(): iterable {
        yield ['0', '0', '0'];
        yield ['0099', '0001', '100'];
        yield ['999', '1', '1000'];
        yield ['1000', '999', '1999'];
        yield ['9223372036854775807', '1', '9223372036854775808'];
        yield ['10000000000000000002', '8', '10000000000000000010'];
        yield [str_repeat('9', 200), '1', '1' . str_repeat('0', 200)];
    }
    
    public function testComparisonDoesNotRoundNumericStrings(): void {
        $a = new ByteCount('10000000000000000002');
        $b = new ByteCount('10000000000000000003');
        $this->assertSame(-1, $a->compare($b));
        $this->assertSame(1, $b->compare($a));
        $this->assertSame(0, $a->compare(new ByteCount('0010000000000000000002')));
        $this->assertSame(-1, (new ByteCount('9'))->compare(new ByteCount('10')));
        $this->assertTrue((new ByteCount('000'))->isZero());
    }
    
    public function testNativeIntegerBoundary(): void {
        $limit = new ByteCount(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $limit->toInt());
        $this->expectException(OverflowException::class);
        $limit->add(new ByteCount(1))->toInt();
    }
    
    public function testUnderflowIsRejected(): void {
        $this->expectException(UnderflowException::class);
        (new ByteCount(0))->subtract(new ByteCount(1));
    }
    
    /** @dataProvider invalidProvider */
    public function testInvalidCountsAreRejected(string $value): void {
        $this->expectException(InvalidArgumentException::class);
        new ByteCount($value);
    }
    
    public function invalidProvider(): iterable {
        foreach (['', '-1', '+1', '1.0', '1e20', ' 1', "1\n", 'one'] as $value) {
            yield [$value];
        }
    }
    
    /** @noinspection PhpParamsInspection,PhpStrictTypeCheckingInspection */
    public function testFloatsAreRejected(): void {
        $this->expectException(TypeError::class);
        // Deliberately invalid input: even integral floats lose large offsets.
        // @phpstan-ignore argument.type (Testing rejection of floats.)
        new ByteCount(1.0);
    }
}

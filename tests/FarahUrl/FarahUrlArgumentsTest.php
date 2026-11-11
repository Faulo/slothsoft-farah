<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\FarahUrl;

use PHPUnit\Framework\TestCase;

class FarahUrlArgumentsTest extends TestCase {
    
    /**
     *
     * @dataProvider createFromValueListProvider
     * @runInSeparateProcess
     * @param array<array-key, mixed> $values
     */
    public function test_createFromValueList(array $values, string $expected): void {
        $actual = FarahUrlArguments::createFromValueList($values);
        $this->assertEquals($expected, (string) $actual);
    }
    
    /**
     *
     * @dataProvider createFromValueListProvider
     * @runInSeparateProcess
     * @param array<array-key, mixed> $values
     */
    public function test_createFromValueList_usesObjectPool(array $values, string $expected): void {
        $values = FarahUrlArguments::createFromValueList($values);
        $expected = FarahUrlArguments::createFromQuery($expected);
        $this->assertSame($expected, $values);
    }
    
    /**
     * @return iterable<array{array<array-key, mixed>, string}>
     */
    public function createFromValueListProvider(): iterable {
        yield 'numeric zero name' => [[0 => 'value'], '0=value'];
        yield 'negative numeric name' => [[-1 => 'value'], '-1=value'];
        yield 'numeric name with nested list' => [[0 => ['items' => ['a', 'b']]], '0[items][]=a&0[items][]=b'];
        yield 'leading zero name stays distinct' => [['01' => 'value'], '01=value'];
        yield 'build query' => [
            [
                'hello' => 'world'
            ],
            'hello=world'
        ];
        yield 'empty string does not emit equal sign' => [
            [
                'a' => null,
                'b' => ''
            ],
            'a&b'
        ];
        yield 'args are sorted' => [
            [
                'b' => '1',
                'a' => '2'
            ],
            'a=2&b=1'
        ];
        yield 'multi-dimensional args' => [
            [
                'search-query' => [
                    'cost' => [
                        'W',
                        'U',
                        'B'
                    ]
                ]
            ],
            'search-query[cost][]=W&search-query[cost][]=U&search-query[cost][]=B'
        ];
    }
    
    /**
     *
     * @dataProvider createFromQueryProvider
     */
    public function test_createFromQuery(string $query, string $expected): void {
        $actual = FarahUrlArguments::createFromQuery($query);
        $this->assertEquals($expected, (string) $actual);
    }
    
    /**
     *
     * @dataProvider createFromQueryProvider
     */
    public function test_createFromQuery_usesObjectPool(string $query, string $expected): void {
        $query = FarahUrlArguments::createFromQuery($query);
        $expected = FarahUrlArguments::createFromQuery($expected);
        $this->assertSame($expected, $query);
    }
    
    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function createFromQueryProvider(): iterable {
        yield 'build query' => [
            'hello=world',
            'hello=world'
        ];
        yield 'can parse without equal sign' => [
            'a&b=',
            'a&b'
        ];
        yield 'args are sorted' => [
            'b=1&a=2',
            'a=2&b=1'
        ];
    }
    
    public function testCreateFromMany(): void {
        $data1 = [
            'a' => 'b'
        ];
        $data2 = [
            'c' => 'd'
        ];
        $data3 = [
            'c' => 'e'
        ];
        
        $args1 = FarahUrlArguments::createFromValueList($data1);
        $args2 = FarahUrlArguments::createFromValueList($data2);
        $args3 = FarahUrlArguments::createFromValueList($data3);
        
        $expected = FarahUrlArguments::createFromValueList($data1 + $data2 + $data3);
        $calculated = FarahUrlArguments::createFromMany($args1, $args2, $args3);
        $this->assertEquals($expected, $calculated);
    }
    
    public function testEmptyArrays(): void {
        $data1 = [
            'a' => '1',
            'b' => []
        ];
        $data2 = [
            'a' => '1',
            'c' => []
        ];
        
        $args1 = FarahUrlArguments::createFromValueList($data1);
        $args2 = FarahUrlArguments::createFromValueList($data2);
        
        $this->assertNotEquals($args1, $args2);
    }
    
    public function testWithSameArgument(): void {
        $data = [
            'a' => '1'
        ];
        
        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withArgument('a', '1');
        
        $this->assertSame($url2, $url1);
    }
    
    public function testWithDifferentArgument(): void {
        $data = [
            'a' => '1'
        ];
        
        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withArgument('a', '2');
        
        $this->assertNotSame($url2, $url1);
        $this->assertEquals('1', $url1->get('a'));
        $this->assertEquals('2', $url2->get('a'));
    }
    
    public function testWithDifferentArguments(): void {
        $data = [
            'a' => '1'
        ];
        
        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withArgument('b', '2');
        
        $this->assertNotSame($url2, $url1);
        $this->assertEquals('1', $url1->get('a'));
        $this->assertFalse($url1->has('b'));
        $this->assertEquals('1', $url2->get('a'));
        $this->assertEquals('2', $url2->get('b'));
    }
    
    public function testWithoutSameArgument(): void {
        $data = [
            'a' => '1'
        ];
        
        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withoutArgument('a');
        
        $this->assertNotSame($url2, $url1);
        $this->assertEquals('1', $url1->get('a'));
        $this->assertFalse($url2->has('a'));
    }
    
    public function testWithoutDifferentArgument(): void {
        $data = [
            'a' => '1'
        ];
        
        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withoutArgument('b');
        
        $this->assertSame($url2, $url1);
    }
}


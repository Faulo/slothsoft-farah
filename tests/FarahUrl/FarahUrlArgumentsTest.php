<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use PHPUnit\Framework\TestCase;

class FarahUrlArgumentsTest extends TestCase {

    public function testCreateFromValueList() {
        $data = [
            'hello' => 'world'
        ];
        $expected = 'hello=world';
        $calculated = FarahUrlArguments::createFromValueList($data);
        $this->assertEquals($expected, (string) $calculated);
    }

    /**
     *
     * @dataProvider argumentsProvider
     */
    public function testCreateFromQuery(FarahUrlArguments $expected, string $query) {
        $calculated = FarahUrlArguments::createFromQuery($query);
        $this->assertEquals($expected, $calculated);
    }

    public function argumentsProvider() {
        $argsList = [];
        $argsList[] = [
            'a' => 'b'
        ];
        $argsList[] = [
            'a' => [
                'b' => [
                    'c',
                    'd',
                    'e'
                ]
            ]
        ];

        $ret = [];
        foreach ($argsList as $args) {
            $query = http_build_query($args);
            $ret[$query] = [
                FarahUrlArguments::createFromValueList($args),
                $query
            ];
        }
        return $ret;
    }

    public function testCreateFromMany() {
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

    public function testEmptyArrays() {
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

        $this->assertEquals((string) $args1, (string) $args2);
        $this->assertNotEquals($args1, $args2);
    }

    public function testWithSameArgument() {
        $data = [
            'a' => '1'
        ];

        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withArgument('a', '1');

        $this->assertSame($url2, $url1);
    }

    public function testWithDifferentArgument() {
        $data = [
            'a' => '1'
        ];

        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withArgument('a', '2');

        $this->assertNotSame($url2, $url1);
        $this->assertEquals('1', $url1->get('a'));
        $this->assertEquals('2', $url2->get('a'));
    }

    public function testWithDifferentArguments() {
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

    public function testWithoutSameArgument() {
        $data = [
            'a' => '1'
        ];

        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withoutArgument('a');

        $this->assertNotSame($url2, $url1);
        $this->assertEquals('1', $url1->get('a'));
        $this->assertFalse($url2->has('a'));
    }

    public function testWithoutDifferentArgument() {
        $data = [
            'a' => '1'
        ];

        $url1 = FarahUrlArguments::createFromValueList($data);
        $url2 = $url1->withoutArgument('b');

        $this->assertSame($url2, $url1);
    }
}


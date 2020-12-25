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
}


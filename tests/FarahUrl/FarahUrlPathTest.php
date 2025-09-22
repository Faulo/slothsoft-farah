<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use PHPUnit\Framework\TestCase;

/**
 * FarahUrlPathTest
 *
 * @see FarahUrlPath
 */
class FarahUrlPathTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FarahUrlPath::class), "Failed to load class 'Slothsoft\Farah\FarahUrl\FarahUrlPath'!");
    }
    
    /**
     *
     * @dataProvider pathSegmentProvider
     */
    public function test_createFromSegments(FarahUrlPath $expected, array $segments): void {
        $actual = FarahUrlPath::createFromSegments($segments);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     *
     * @dataProvider pathSegmentProvider
     */
    public function test_getSegments(FarahUrlPath $path, array $expected): void {
        $actual = $path->getSegments();
        
        $this->assertEquals($expected, $actual);
    }
    
    public function pathSegmentProvider(): iterable {
        yield 'empty is empty' => [
            FarahUrlPath::createEmpty(),
            []
        ];
        yield 'empty string is empty' => [
            FarahUrlPath::createFromString(''),
            []
        ];
        yield '/ is empty' => [
            FarahUrlPath::createFromString('/'),
            []
        ];
        yield '/one is one' => [
            FarahUrlPath::createFromString('/one'),
            [
                'one'
            ]
        ];
        yield '/one/ is one' => [
            FarahUrlPath::createFromString('/one'),
            [
                'one'
            ]
        ];
        yield '/1/2/3 is [1, 2, 3]' => [
            FarahUrlPath::createFromString('/1/2/3'),
            [
                '1',
                '2',
                '3'
            ]
        ];
    }
}
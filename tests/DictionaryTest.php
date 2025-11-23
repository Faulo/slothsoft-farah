<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * DictionaryTest
 *
 * @see Dictionary
 */
class DictionaryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Dictionary::class), "Failed to load class 'Slothsoft\Farah\Dictionary'!");
    }
    
    /**
     *
     * @dataProvider sanitationProvider
     */
    public function test_xsltSanitizeKey(string $input, string $expected): void {
        $actual = Dictionary::xsltSanitizeKey($input);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    public function sanitationProvider(): iterable {
        yield 'empty string' => [
            '',
            ''
        ];
        
        yield 'words stay' => [
            'abcöäüß',
            'abcöäüß'
        ];
        
        yield 'letters stay' => [
            'id023',
            'id023'
        ];
        
        yield 'whitespace go' => [
            "  abc ö \t ä \n ü \r ß  ",
            'abcöäüß'
        ];
        
        yield 'only .-_ stay' => [
            'id.-_:@$%&/\\+,;()[]{}?',
            'id.-_'
        ];
        
        yield 'kanji' => [
            '和　製　漢　字',
            '和製漢字'
        ];
        
        yield 'only letter at beginning' => [
            '0.-A',
            'A'
        ];
        
        yield 'only _ at beginning' => [
            '0.-_',
            '_'
        ];
        
        yield 'middle dot' => [
            'A·B',
            'A·B'
        ];
    }
}
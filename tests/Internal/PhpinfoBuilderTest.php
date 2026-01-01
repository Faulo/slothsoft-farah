<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * PhpinfoBuilderTest
 *
 * @see PhpinfoBuilder
 */
final class PhpinfoBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(PhpinfoBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\PhpinfoBuilder'!");
    }
    
    private const REFERENCE = 'farah://slothsoft@farah/phpinfo';
    
    private static function getPhpInfo(): string {
        ob_start();
        phpinfo();
        $data = ob_get_contents();
        ob_end_clean();
        
        return '<pre>' . htmlentities($data, ENT_XML1 | ENT_DISALLOWED, 'UTF-8') . '</pre>';
    }
    
    /**
     *
     * @dataProvider countProvider
     */
    public function test_read(int $count): void {
        for ($i = 0; $i < $count; $i ++) {
            $actual = file_get_contents(self::REFERENCE);
            $this->assertThat($actual, new IsEqual(self::getPhpInfo()));
        }
    }
    
    public function countProvider(): iterable {
        yield 'once' => [
            1
        ];
        yield 'twice' => [
            2
        ];
        yield 'thrice' => [
            3
        ];
    }
}
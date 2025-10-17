<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsIdentical;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * ExecutableTest
 *
 * @see Executable
 */
class ExecutableTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Executable::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\Executable'!");
    }
    
    /**
     *
     * @dataProvider sameResultProvider
     */
    public function test_fragment_doesNotChangeResult(string $url, string $fragment): void {
        $url = FarahUrl::createFromReference($url, Module::getBaseUrl());
        
        $left = Module::resolveToResult($url);
        $right = Module::resolveToResult($url->withFragment($fragment));
        
        $this->assertThat($left, new IsIdentical($right));
    }
    
    public function sameResultProvider(): iterable {
        yield 'phpinfo default' => [
            '/phpinfo',
            ''
        ];
        yield 'phpinfo #xml' => [
            '/phpinfo',
            'xml'
        ];
    }
}
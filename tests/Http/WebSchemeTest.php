<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * WebSchemeTest
 *
 * @see WebScheme
 */
final class WebSchemeTest extends TestCase {
    
    /**
     *
     * @dataProvider supportedSchemeProvider
     */
    public function testNormalize(string $input, string $expected): void {
        $this->assertSame($expected, WebScheme::normalize($input));
    }
    
    public function supportedSchemeProvider(): iterable {
        yield 'HTTP' => [
            'HTTP',
            WebScheme::HTTP
        ];
        yield 'HTTPS' => [
            'HTTPS',
            WebScheme::HTTPS
        ];
    }
    
    public function testNormalizeRejectsUnsupportedScheme(): void {
        $this->expectException(InvalidArgumentException::class);
        WebScheme::normalize('ftp');
    }
}

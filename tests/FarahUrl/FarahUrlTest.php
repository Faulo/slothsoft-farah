<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\FileSystem;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;

class FarahUrlTest extends TestCase {
    
    /**
     *
     * @dataProvider malformedUrlProvider
     */
    public function testMalformedUrlParsing(string $ref): void {
        $this->expectException(MalformedUrlException::class);
        FarahUrl::createFromReference($ref);
    }
    
    public function malformedUrlProvider() {
        $urls = [];
        $urls[] = 'farah:///slothsoft@farah';
        $urls[] = 'farah://slothsoft@farah:port';
        $urls[] = 'farah://slothsoft@';
        
        foreach ($urls as $ref) {
            yield $ref => [
                $ref
            ];
        }
    }
    
    /**
     *
     * @dataProvider incompleteUrlProvider
     */
    public function testIncompleteUrlParsing(string $ref): void {
        $this->expectException(IncompleteUrlException::class);
        FarahUrl::createFromReference($ref);
    }
    
    public function incompleteUrlProvider(): iterable {
        $urls = [];
        $urls[] = '//slothsoft@farah';
        $urls[] = 'farah://slothsoft';
        $urls[] = 'farah://@farah';
        
        foreach ($urls as $ref) {
            yield $ref => [
                $ref
            ];
        }
    }
    
    /**
     *
     * @dataProvider notFarahUrlProvider
     */
    public function testNotFarahUrlParsing(string $ref): void {
        $this->expectException(ProtocolNotSupportedException::class);
        FarahUrl::createFromReference($ref);
    }
    
    public function notFarahUrlProvider(): iterable {
        $urls = [];
        $urls[] = 'http://slothsoft@farah';
        $urls[] = 'file://slothsoft@farah';
        
        foreach ($urls as $ref) {
            yield $ref => [
                $ref
            ];
        }
    }
    
    /**
     *
     * @dataProvider absoluteUrlProvider
     */
    public function testAbsoluteUrlParsing(string $expected, string $ref): void {
        $url = FarahUrl::createFromReference($ref);
        $this->assertEquals($expected, (string) $url);
    }
    
    public function absoluteUrlProvider(): iterable {
        $urls = [];
        $urls['farah://slothsoft@farah'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/tmp/..'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./#'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./#xml'] = 'farah://slothsoft@farah/#xml';
        
        $urls['farah://slothsoft@farah/asset'] = 'farah://slothsoft@farah/asset';
        $urls['farah://slothsoft@farah/asset/'] = 'farah://slothsoft@farah/asset';
        $urls['farah://slothsoft@farah/asset/tmp/..'] = 'farah://slothsoft@farah/asset';
        
        foreach ($urls as $ref => $expected) {
            yield $ref => [
                $expected,
                $ref
            ];
        }
    }
    
    /**
     *
     * @dataProvider relativeUrlProvider
     */
    public function testRelativeUrlParsing(string $expected, string $ref): void {
        $authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'farah');
        $path = FarahUrlPath::createFromString('/testing');
        $url = FarahUrl::createFromComponents($authority, $path);
        $url = FarahUrl::createFromReference($ref, $url);
        $this->assertEquals($expected, (string) $url);
    }
    
    public function relativeUrlProvider(): iterable {
        $urls = [];
        $urls['farah://slothsoft@farah/assets'] = 'farah://slothsoft@farah/assets';
        $urls['//slothsoft@farah/assets'] = 'farah://slothsoft@farah/assets';
        $urls['//farah/assets'] = 'farah://slothsoft@farah/assets';
        $urls['/assets'] = 'farah://slothsoft@farah/assets';
        
        $urls['farah://slothsoft@farah'] = 'farah://slothsoft@farah/';
        $urls['//slothsoft@farah'] = 'farah://slothsoft@farah/';
        $urls['//farah'] = 'farah://slothsoft@farah/';
        $urls['//farah#'] = 'farah://slothsoft@farah/';
        $urls['//farah#xml'] = 'farah://slothsoft@farah/#xml';
        
        $urls['/'] = 'farah://slothsoft@farah/';
        $urls['..'] = 'farah://slothsoft@farah/';
        $urls['./..'] = 'farah://slothsoft@farah/';
        
        $urls[''] = 'farah://slothsoft@farah/testing';
        $urls['.'] = 'farah://slothsoft@farah/testing';
        $urls['./'] = 'farah://slothsoft@farah/testing';
        $urls['./.'] = 'farah://slothsoft@farah/testing';
        
        $urls['assets'] = 'farah://slothsoft@farah/testing/assets';
        $urls['./../testing/assets'] = 'farah://slothsoft@farah/testing/assets';
        $urls['/testing/assets'] = 'farah://slothsoft@farah/testing/assets';
        $urls['./assets/tmp/..'] = 'farah://slothsoft@farah/testing/assets';
        
        foreach ($urls as $ref => $expected) {
            yield "'$ref'" => [
                $expected,
                $ref
            ];
        }
    }
    
    /**
     */
    public function testFileModifiedTime(): void {
        $assetsPath = realpath('assets/xsl/module.xsl');
        $this->assertIsString($assetsPath);
        
        $assetsUrl = 'farah://slothsoft@farah/xsl/module';
        $dateFormat = 'd.m.y H:i';
        
        $expected = FileSystem::changetime($assetsPath);
        $actual = FileSystem::changetime($assetsUrl);
        
        $this->assertEquals(date($dateFormat, $expected), date($dateFormat, $actual));
    }
    
    /**
     *
     * @dataProvider componentProvider
     */
    public function testCreateFromComponents(string $expectedUrl, $authority, $path, $args, $fragment): void {
        $expectedUrl = FarahUrl::createFromReference($expectedUrl);
        $actualUrl = FarahUrl::createFromComponents($authority, $path, $args, $fragment);
        
        $this->assertEquals($expectedUrl, $actualUrl);
    }
    
    public function componentProvider(): iterable {
        yield 'all strings' => [
            'farah://slothsoft@farah/some/path?hello=world#xml',
            'slothsoft@farah',
            'some/path',
            'hello=world',
            'xml'
        ];
        yield 'authority object' => [
            'farah://slothsoft@farah/some/path?hello=world#xml',
            FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'farah'),
            'some/path',
            'hello=world',
            'xml'
        ];
        yield 'path object' => [
            'farah://slothsoft@farah/some/path?hello=world#xml',
            'slothsoft@farah',
            FarahUrlPath::createFromString('some/path'),
            'hello=world',
            'xml'
        ];
        yield 'args object' => [
            'farah://slothsoft@farah/some/path?hello=world#xml',
            'slothsoft@farah',
            'some/path',
            FarahUrlArguments::createFromValueList([
                'hello' => 'world'
            ]),
            'xml'
        ];
        yield 'fragment object' => [
            'farah://slothsoft@farah/some/path?hello=world#xml',
            'slothsoft@farah',
            'some/path',
            'hello=world',
            FarahUrlStreamIdentifier::createFromString('xml')
        ];
        yield 'null path' => [
            'farah://slothsoft@farah',
            'slothsoft@farah',
            null,
            null,
            null
        ];
        yield 'empty path' => [
            'farah://slothsoft@farah',
            'slothsoft@farah',
            '',
            null,
            null
        ];
        yield 'slash path' => [
            'farah://slothsoft@farah',
            'slothsoft@farah',
            '/',
            null,
            null
        ];
        yield 'null path /' => [
            'farah://slothsoft@farah/',
            'slothsoft@farah',
            null,
            null,
            null
        ];
        yield 'empty path /' => [
            'farah://slothsoft@farah/',
            'slothsoft@farah',
            '',
            null,
            null
        ];
        yield 'slash path /' => [
            'farah://slothsoft@farah/',
            'slothsoft@farah',
            '/',
            null,
            null
        ];
    }
}


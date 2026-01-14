<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\FileSystem;
use Slothsoft\FarahTesting\TestUtils;
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
        
        $this->assertSame(FarahUrl::createFromReference($expected), $url);
    }
    
    public function absoluteUrlProvider(): iterable {
        $urls = [];
        $urls['farah://slothsoft@farah'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/tmp/..'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/.'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./#'] = 'farah://slothsoft@farah/';
        $urls['farah://slothsoft@farah/./#xml'] = 'farah://slothsoft@farah/#xml';
        
        $urls['farah://slothsoft@farah/asset'] = 'farah://slothsoft@farah/asset';
        $urls['farah://slothsoft@farah/asset/'] = 'farah://slothsoft@farah/asset';
        $urls['farah://slothsoft@farah/asset/tmp/..'] = 'farah://slothsoft@farah/asset';
        $urls['farah://slothsoft@farah/asset/tmp/../'] = 'farah://slothsoft@farah/asset';
        
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
        
        $this->assertSame(FarahUrl::createFromReference($expected), $url);
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
        TestUtils::changeWorkingDirectoryToComposerRoot();
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
        
        $this->assertSame($expectedUrl, $actualUrl);
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
    
    /**
     *
     * @dataProvider withAdditionalQueryArgumentsProvider
     */
    public function test_withAdditionalQueryArguments(string $url, string $query, string $expected): void {
        $url = FarahUrl::createFromReference($url);
        $args = FarahUrlArguments::createFromQuery($query);
        $expected = FarahUrl::createFromReference($expected);
        
        $this->assertSame($expected, $url->withAdditionalQueryArguments($args));
    }
    
    public function withAdditionalQueryArgumentsProvider(): iterable {
        yield 'no query stays' => [
            'farah://slothsoft@farah/?a=b',
            '',
            'farah://slothsoft@farah/?a=b'
        ];
        yield 'add query adds' => [
            'farah://slothsoft@farah/?a=b',
            'c=d',
            'farah://slothsoft@farah/?a=b&c=d'
        ];
        yield 'add query sorts' => [
            'farah://slothsoft@farah/?c=d',
            'a=b',
            'farah://slothsoft@farah/?a=b&a=b&c=d'
        ];
        yield 'add query overwrites' => [
            'farah://slothsoft@farah/?a=b',
            'a=1',
            'farah://slothsoft@farah/?a=1'
        ];
        yield 'add query overwrites null' => [
            'farah://slothsoft@farah/?a=b',
            'a=',
            'farah://slothsoft@farah/?a'
        ];
        yield 'add query overwrites array' => [
            'farah://slothsoft@farah/?a[]=b',
            'a[]=1',
            'farah://slothsoft@farah/?a[]=1'
        ];
    }
    
    /**
     *
     * @dataProvider withAdditionalQueryArguments_overwriteExistingProvider
     */
    public function test_withAdditionalQueryArguments_overwriteExisting(string $url, string $query, string $expected, bool $overwriteExisting): void {
        $url = FarahUrl::createFromReference($url);
        $args = FarahUrlArguments::createFromQuery($query);
        $expected = FarahUrl::createFromReference($expected);
        
        $this->assertSame($expected, $url->withAdditionalQueryArguments($args, $overwriteExisting));
    }
    
    public function withAdditionalQueryArguments_overwriteExistingProvider(): iterable {
        yield 'add query overwrites' => [
            'farah://slothsoft@farah/?a=b',
            'a=1',
            'farah://slothsoft@farah/?a=1',
            true
        ];
        yield 'add query overwrites null' => [
            'farah://slothsoft@farah/?a=b',
            'a=',
            'farah://slothsoft@farah/?a',
            true
        ];
        yield 'add query overwrites array' => [
            'farah://slothsoft@farah/?a[]=b',
            'a[]=1',
            'farah://slothsoft@farah/?a[]=1',
            true
        ];
        
        yield 'add query does not overwrite' => [
            'farah://slothsoft@farah/?a=b',
            'a=1',
            'farah://slothsoft@farah/?a=b',
            false
        ];
        yield 'add query does not overwrite null' => [
            'farah://slothsoft@farah/?a=b',
            'a=',
            'farah://slothsoft@farah/?a=b',
            false
        ];
        yield 'add query does not overwrite array' => [
            'farah://slothsoft@farah/?a[]=b',
            'a[]=1',
            'farah://slothsoft@farah/?a[]=b',
            false
        ];
    }
    
    /**
     *
     * @dataProvider fragmentProvider
     */
    public function test_getFragment(string $url, string $expected): void {
        $url = FarahUrl::createFromReference($url);
        $actual = $url->getFragment();
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    public function fragmentProvider(): iterable {
        yield 'no fragment is empty string' => [
            'farah://slothsoft@farah/?a=b',
            ''
        ];
        
        yield 'empty fragment is empty string' => [
            'farah://slothsoft@farah/?a=b#',
            ''
        ];
        
        yield 'fragment is something' => [
            'farah://slothsoft@farah/?a=b#xml',
            'xml'
        ];
    }
}


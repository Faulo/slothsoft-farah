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
    public function testMalformedUrlParsing(string $ref) {
        $this->expectException(MalformedUrlException::class);
        FarahUrl::createFromReference($ref);
    }

    public function malformedUrlProvider() {
        $urls = [];
        $urls[] = 'farah:///slothsoft@farah';
        $urls[] = 'farah://slothsoft@farah:port';
        $urls[] = 'farah://slothsoft@';

        $ret = [];
        foreach ($urls as $ref) {
            $ret[$ref] = [
                $ref
            ];
        }
        return $ret;
    }

    /**
     *
     * @dataProvider incompleteUrlProvider
     */
    public function testIncompleteUrlParsing(string $ref) {
        $this->expectException(IncompleteUrlException::class);
        FarahUrl::createFromReference($ref);
    }

    public function incompleteUrlProvider() {
        $urls = [];
        $urls[] = '//slothsoft@farah';
        $urls[] = 'farah://slothsoft';
        $urls[] = 'farah://@farah';

        $ret = [];
        foreach ($urls as $ref) {
            $ret[$ref] = [
                $ref
            ];
        }
        return $ret;
    }

    /**
     *
     * @dataProvider notFarahUrlProvider
     */
    public function testNotFarahUrlParsing(string $ref) {
        $this->expectException(ProtocolNotSupportedException::class);
        FarahUrl::createFromReference($ref);
    }

    public function notFarahUrlProvider() {
        $urls = [];
        $urls[] = 'http://slothsoft@farah';
        $urls[] = 'file://slothsoft@farah';

        $ret = [];
        foreach ($urls as $ref) {
            $ret[$ref] = [
                $ref
            ];
        }
        return $ret;
    }

    /**
     *
     * @dataProvider absoluteUrlProvider
     */
    public function testAbsoluteUrlParsing(string $expected, string $ref) {
        $url = FarahUrl::createFromReference($ref);
        $this->assertEquals($expected, (string) $url);
    }

    public function absoluteUrlProvider() {
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

        $ret = [];
        foreach ($urls as $ref => $expected) {
            $ret[$ref] = [
                $expected,
                $ref
            ];
        }
        return $ret;
    }

    /**
     *
     * @dataProvider relativeUrlProvider
     */
    public function testRelativeUrlParsing(string $expected, string $ref) {
        $authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'farah');
        $path = FarahUrlPath::createFromString('/testing');
        $url = FarahUrl::createFromComponents($authority, $path);
        $url = FarahUrl::createFromReference($ref, $url);
        $this->assertEquals($expected, (string) $url);
    }

    public function relativeUrlProvider() {
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

        $ret = [];
        foreach ($urls as $ref => $expected) {
            $ret["'$ref'"] = [
                $expected,
                $ref
            ];
        }
        return $ret;
    }

    /**
     */
    public function testFileModifiedTime() {
        $assetsPath = realpath('assets/xsl/module.xsl');
        $this->assertIsString($assetsPath);

        $assetsUrl = 'farah://slothsoft@farah/xsl/module';
        $dateFormat = 'd.m.y H:i';

        $expected = FileSystem::changetime($assetsPath);
        $actual = FileSystem::changetime($assetsUrl);

        $this->assertEquals(date($dateFormat, $expected), date($dateFormat, $actual));
    }
}


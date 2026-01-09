<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

class StreamTest extends TestCase {
    
    private const TEST_ACTIVE = true;
    
    public function someFarahAssets(): iterable {
        if (self::TEST_ACTIVE) {
            yield 'farah://slothsoft@farah/' => [
                'farah://slothsoft@farah/',
                'dynamic'
            ];
            yield 'farah://slothsoft@farah/js/DOMHelper' => [
                'farah://slothsoft@farah/js/DOM',
                'file'
            ];
            yield 'farah://slothsoft@farah/example-page' => [
                'farah://slothsoft@farah/example-page',
                'xslt'
            ];
        }
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider someFarahAssets
     */
    public function test_FarahAssetStream_getContents(string $ref, string $type): void {
        $url = FarahUrl::createFromReference($ref);
        
        $stream = Module::resolveToStreamWriter($url)->toStream();
        
        $this->assertThat((string) $stream, new IsEqual($this->getExpectedContent($ref, $type)));
        $this->assertThat((string) $stream, new IsEqual($this->getExpectedContent($ref, $type)));
    }
    
    private function getExpectedContent(string $ref, string $type): string {
        return file_get_contents($ref);
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider someFarahAssets
     */
    public function test_FarahAssetStream_getSize(string $ref, string $type): void {
        $url = FarahUrl::createFromReference($ref);
        
        $stream = Module::resolveToStreamWriter($url)->toStream();
        
        $this->assertThat($stream->getSize(), new IsEqual($this->getExpectedSize($ref, $type)));
        $this->assertThat($stream->getSize(), new IsEqual($this->getExpectedSize($ref, $type)));
    }
    
    private function getExpectedSize(string $ref, string $type): int {
        return strlen($this->getExpectedContent($ref, $type));
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider someFarahAssets
     */
    public function test_FarahAssetStream_isSeekable(string $ref, string $type): void {
        $url = FarahUrl::createFromReference($ref);
        
        $stream = Module::resolveToStreamWriter($url)->toStream();
        
        $this->assertThat($stream->isSeekable(), new IsEqual($this->getExpectedSeekable($ref, $type)));
        $this->assertThat($stream->isSeekable(), new IsEqual($this->getExpectedSeekable($ref, $type)));
    }
    
    private function getExpectedSeekable(string $ref, string $type): bool {
        return true;
    }
}
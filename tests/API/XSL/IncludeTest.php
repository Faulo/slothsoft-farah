<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

final class IncludeTest extends TestCase {
    
    private FarahUrl $base;
    
    public function setUp(): void {
        $this->base = FarahUrl::createFromReference('farah://slothsoft@test-module');
        Module::registerWithXmlManifestAndDefaultAssets($this->base->getAssetAuthority(), __DIR__ . '/../../../test-files/test-module');
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_linkingResult(string $path, string $expectedFile): void {
        $url = FarahUrl::createFromReference($path, $this->base);
        
        $actual = DOMHelper::loadDocument((string) $url);
        
        $expected = DOMHelper::loadDocument($expectedFile);
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    public function exampleProvider(): iterable {
        yield 'no include' => [
            '/xsl/import',
            __DIR__ . '/../../../test-files/test-module/xsl/import.xsl'
        ];
        
        yield 'include' => [
            '/xsl/import?includes=embed',
            __DIR__ . '/../../../test-files/test-module/xsl/import-embedded.xsl'
        ];
    }
}
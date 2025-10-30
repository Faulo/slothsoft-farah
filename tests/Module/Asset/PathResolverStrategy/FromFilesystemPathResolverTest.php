<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * FromFilesystemPathResolverTest
 *
 * @see FromFilesystemPathResolver
 */
class FromFilesystemPathResolverTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FromFilesystemPathResolver::class), "Failed to load class 'Slothsoft\Farah\Module\Asset\PathResolverStrategy\FromFilesystemPathResolver'!");
    }
    
    private const DIRECTORY = 'test-files/test-paths';
    
    /**
     *
     * @dataProvider loadChildrenProvider
     */
    public function test_loadChildren(?string $mimeType, array $expected): void {
        $root = LeanElement::createOneFromArray(Manifest::TAG_RESOURCE_DIRECTORY, [
            Manifest::ATTR_TYPE => $mimeType,
            Manifest::ATTR_REALPATH => realpath(self::DIRECTORY)
        ]);
        $context = $this->createStub(AssetInterface::class);
        $context->method('getManifestElement')->willReturn($root);
        
        $sut = new FromFilesystemPathResolver();
        $actual = $sut->loadChildren($context);
        $actual = [
            ...$actual
        ];
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    public function loadChildrenProvider(): iterable {
        yield 'no type loads all files' => [
            null,
            [
                'asset.txt',
                'asset.xml',
                'asset.xsl',
                'directory',
                '日本語.xml'
            ]
        ];
        
        yield 'mime type with 1 possible extension skips extension' => [
            'application/xml',
            [
                'asset',
                'directory',
                '日本語'
            ]
        ];
        
        yield 'mime type with multiple extensions' => [
            'application/*',
            [
                'asset.xml',
                'asset.xsl',
                'directory',
                '日本語.xml'
            ]
        ];
    }
    
    /**
     *
     * @dataProvider pathProvider
     */
    public function test_resolvePath(string $url, ?string $exception = null): void {
        if ($exception) {
            $this->expectException($exception);
            Module::resolveToFileWriter(FarahUrl::createFromReference($url));
        } else {
            $file = Module::resolveToFileWriter(FarahUrl::createFromReference($url))->toFile();
            $this->assertFileExists($file->getRealPath());
        }
    }
    
    public function pathProvider(): iterable {
        yield 'valid directory' => [
            'farah://slothsoft@farah/js'
        ];
        
        yield 'valid asset' => [
            'farah://slothsoft@farah/js/DOM'
        ];
        
        yield 'missing asset' => [
            'farah://slothsoft@farah/js/missing-asset',
            AssetPathNotFoundException::class
        ];
    }
}
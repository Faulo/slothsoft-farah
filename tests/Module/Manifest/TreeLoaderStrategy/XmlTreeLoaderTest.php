<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

/**
 * @see XmlTreeLoader
 */
final class XmlTreeLoaderTest extends TestCase {
    private string $manifestPath;
    
    private string $cachePath;
    
    private ?SplFileInfo $cacheFile = null;
    
    private ManifestInterface $context;
    
    /**
     * @throws Exception
     */
    protected function setUp(): void {
        $directory = temp_dir(__CLASS__);
        $this->manifestPath = $directory . DIRECTORY_SEPARATOR . 'manifest.xml';
        $this->cachePath = temp_file(__CLASS__);
        
        $loaderFile = (new ReflectionClass(XmlTreeLoader::class))->getFileName();
        $this->assertIsString($loaderFile);
        $this->context = $this->createMock(ManifestInterface::class);
        $this->context->method('createManifestFile')->with('manifest.xml')->willReturnCallback(
            fn() => new SplFileInfo($this->manifestPath)
        );
        $this->context->method('createCacheFile')->willReturnCallback(
            function (string $fileName, $path, FarahUrlArguments $arguments) use ($loaderFile): SplFileInfo {
                $this->assertSame('manifest.tmp', $fileName);
                $this->assertNull($path);
                $this->assertSame(realpath($this->manifestPath), $arguments->get('path'));
                $this->assertSame(hash_file('sha256', $loaderFile), $arguments->get('loader'));
                return $this->cacheFile ?? new SplFileInfo($this->cachePath);
            }
        );
    }
    
    /**
     * @test
     * @dataProvider provideChangedTimestamps
     */
    public function invalidatesCacheWhenSourceTimestampChanges(int $firstOffset, int $secondOffset): void {
        $timestamp = time() - 3600;
        $this->writeManifest('first', $timestamp + $firstOffset);
        
        $loader = new XmlTreeLoader();
        $this->assertSame([
            'first'
        ], $this->getChildNames($loader->loadTree($this->context)));
        $this->assertCacheTimestamp($timestamp + $firstOffset);
        
        $this->writeManifest('second', $timestamp + $secondOffset);
        
        $this->assertSame([
            'second'
        ], $this->getChildNames($loader->loadTree($this->context)));
        $this->assertCacheTimestamp($timestamp + $secondOffset);
    }
    
    /**
     * @return iterable<array{0: int, 1: int}>
     */
    public static function provideChangedTimestamps(): iterable {
        yield 'moves forwards' => [
            0,
            60
        ];
        yield 'moves backwards' => [
            0,
            -60
        ];
    }
    
    /**
     * @test
     */
    public function reusesCacheWhileSourceTimestampIsUnchanged(): void {
        $timestamp = time() - 3600;
        $this->writeManifest('cached', $timestamp);
        
        $loader = new XmlTreeLoader();
        $loader->loadTree($this->context);
        $this->writeManifest('not-cached', $timestamp);
        
        $this->assertSame([
            'cached'
        ], $this->getChildNames($loader->loadTree($this->context)));
    }
    
    /** @return iterable<string, array{string}> */
    public static function provideInvalidCacheContents(): iterable {
        yield 'truncated serialization' => ['O:'];
        yield 'empty file' => [''];
        yield 'serialized null' => ['N;'];
        yield 'serialized false' => ['b:0;'];
        yield 'serialized array' => ['a:0:{}'];
        yield 'unexpected object' => ['O:8:"stdClass":0:{}'];
    }
    
    /** @dataProvider provideInvalidCacheContents */
    public function testRebuildsInvalidCache(string $contents): void {
        $timestamp = time() - 3600;
        $this->writeManifest('recovered', $timestamp);
        file_put_contents($this->cachePath, $contents);
        touch($this->cachePath, $timestamp);
        clearstatcache(true, $this->cachePath);
        $this->assertRecoveredManifest($timestamp);
    }
    
    public function testRebuildsWhenCacheReadFailsAfterMetadataCheck(): void {
        $timestamp = time() - 3600;
        $this->writeManifest('recovered', $timestamp);
        clearstatcache(true, $this->cachePath);
        // Simulate a cache disappearing between the metadata check and the read.
        $cacheFile = $this->getMockBuilder(SplFileInfo::class)
            ->setConstructorArgs([$this->cachePath])
            ->onlyMethods(['isFile', 'getMTime'])
            ->getMock();
        $cacheFile->method('isFile')->willReturn(true);
        $cacheFile->method('getMTime')->willReturn($timestamp);
        $this->cacheFile = $cacheFile;
        $this->assertRecoveredManifest($timestamp);
    }
    
    private function assertRecoveredManifest(int $timestamp): void {
        $loader = new XmlTreeLoader();
        $root = $loader->loadTree($this->context);
        $this->assertSame(['recovered'], $this->getChildNames($root));
        $this->assertCacheTimestamp($timestamp);
        $this->writeManifest('not-cached', $timestamp);
        $this->assertSame(['recovered'], $this->getChildNames((new XmlTreeLoader())->loadTree($this->context)));
    }
    
    private function writeManifest(string $childName, int $timestamp): void {
        $xml = sprintf(
            '<?xml version="1.0"?><assets><fragment name="%s" /></assets>',
            $childName
        );
        file_put_contents($this->manifestPath, $xml);
        touch($this->manifestPath, $timestamp);
        clearstatcache(true, $this->manifestPath);
    }
    
    private function assertCacheTimestamp(int $timestamp): void {
        clearstatcache(true, $this->cachePath);
        $this->assertSame($timestamp, filemtime($this->cachePath));
    }
    
    /**
     * @return list<string>
     */
    private function getChildNames(LeanElement $root): array {
        $names = [];
        foreach ($root->getChildren() as $child) {
            $names[] = $child->getAttribute('name');
        }
        return $names;
    }
}

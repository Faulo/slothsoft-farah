<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

/**
 * @see FromSubManifestPathResolver
 */
final class FromSubManifestPathResolverTest extends TestCase {
    private const DIRECTORY = 'nested';

    private string $manifestPath;

    private string $cachePath;

    private AssetInterface $context;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        $directory = temp_dir(__CLASS__);
        $manifestDirectory = $directory . DIRECTORY_SEPARATOR . self::DIRECTORY;
        mkdir($manifestDirectory);
        $this->manifestPath = $manifestDirectory . DIRECTORY_SEPARATOR . 'manifest.xml';
        $this->cachePath = temp_file(__CLASS__);

        $element = LeanElement::createOneFromArray('manifest-directory', [
            Manifest::ATTR_ID => 'probe@sub-manifest',
            Manifest::ATTR_NAME => self::DIRECTORY,
            Manifest::ATTR_ASSETPATH => self::DIRECTORY,
            Manifest::ATTR_PATH => self::DIRECTORY,
            Manifest::ATTR_REALPATH => $manifestDirectory
        ]);
        $loaderFile = (new ReflectionClass(FromSubManifestPathResolver::class))->getFileName();

        $manifest = $this->createMock(ManifestInterface::class);
        $manifest->method('createManifestFile')->with(self::DIRECTORY . DIRECTORY_SEPARATOR . 'manifest.xml')->willReturnCallback(
            fn () => new SplFileInfo($this->manifestPath)
        );
        $manifest->method('createCacheFile')->willReturnCallback(
            function (string $fileName, $path, FarahUrlArguments $arguments) use ($loaderFile): SplFileInfo {
                $this->assertSame('manifest.tmp', $fileName);
                $this->assertSame(self::DIRECTORY, $path);
                $this->assertSame(realpath($this->manifestPath), $arguments->get('path'));
                $this->assertSame(hash_file('sha256', $loaderFile), $arguments->get('loader'));
                return new SplFileInfo($this->cachePath);
            }
        );

        $this->context = $this->createMock(AssetInterface::class);
        $this->context->method('getManifest')->willReturn($manifest);
        $this->context->method('getManifestElement')->willReturn($element);
    }

    /**
     * @test
     * @dataProvider provideChangedTimestamps
     */
    public function invalidatesCacheWhenSourceTimestampChanges(int $firstOffset, int $secondOffset): void {
        $timestamp = time() - 3600;
        $this->writeManifest('first', $timestamp + $firstOffset);

        $resolver = new FromSubManifestPathResolver();
        $this->assertSame([
            'first'
        ], iterator_to_array($resolver->loadChildren($this->context)));
        $this->assertCacheTimestamp($timestamp + $firstOffset);

        $this->writeManifest('second', $timestamp + $secondOffset);

        $resolver = new FromSubManifestPathResolver();
        $this->assertSame([
            'second'
        ], iterator_to_array($resolver->loadChildren($this->context)));
        $this->assertCacheTimestamp($timestamp + $secondOffset);
    }

    public static function provideChangedTimestamps(): iterable {
        yield 'moves forwards' => [
            0,
            60
        ];
        yield 'moves backwards' => [
            0,
            - 60
        ];
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
}

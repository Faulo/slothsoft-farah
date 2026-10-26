<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\StreamWrapper;

use Error;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Module\Module;

/**
 * FarahStreamWrapperFactoryTest
 *
 * @see FarahStreamWrapperFactory
 */
class FarahStreamWrapperFactoryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FarahStreamWrapperFactory::class), "Failed to load class 'Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory'!");
    }
    
    /**
     * @dataProvider downloadProvider
     * @runInSeparateProcess
     */
    public function testStatUrlReturnsTheSelectedDownloadFileStatistics(string $stage, string $action): void {
        Module::registerWithXmlManifestAndDefaultAssets('test@http-downloads', dirname(__DIR__, 2) . '/test-files/http-downloads');
        $url = 'farah://test@http-downloads/archive?stage=' . $stage . '&action=' . $action;
        $statistics = (new FarahStreamWrapperFactory())->statUrl($url, 0);
        $this->assertIsArray($statistics);
        $this->assertSame(10, $statistics['size']);
        $this->assertSame(0100000, $statistics['mode'] & 0170000);
        
        clearstatcache(true, $url);
        $nativeStatistics = stat($url);
        $this->assertIsArray($nativeStatistics);
        $this->assertSame(10, $nativeStatistics['size']);
        $this->assertTrue(file_exists($url));
        $this->assertSame(10, filesize($url));
    }
    
    public function downloadProvider(): iterable {
        foreach (['asset', 'asset-from-result', 'result', 'direct'] as $stage) {
            foreach (['download', 'show'] as $action) {
                yield "$stage / $action" => [$stage, $action];
            }
        }
    }
    
    /**
     * @dataProvider statusProvider
     * @runInSeparateProcess
     */
    public function testStatUrlMapsHttpStatusesToExistenceWithoutMetadata(int $status): void {
        Module::registerWithXmlManifestAndDefaultAssets('test@http-stat', dirname(__DIR__, 2) . '/test-files/http-stat');
        $url = 'farah://test@http-stat/status?status=' . $status;
        $exists = $status < 400;
        $this->assertSame($exists ? [] : false, (new FarahStreamWrapperFactory())->statUrl($url, 0));
        
        clearstatcache(true, $url);
        $this->assertSame($exists, file_exists($url));
        if ($exists) {
            $statistics = stat($url);
            $this->assertIsArray($statistics);
            $this->assertSame(0, $statistics['size']);
            $this->assertSame(0, $statistics['mode']);
            $this->assertSame(0, filesize($url));
            $this->assertFalse(is_file($url));
            $this->assertFalse(is_dir($url));
        }
    }
    
    public function statusProvider(): iterable {
        yield 'no content' => [204];
        yield 'redirect' => [302];
        yield 'not modified' => [304];
        yield 'last non-error code' => [399];
        yield 'first error code' => [400];
        yield 'not found' => [404];
        yield 'server error' => [500];
    }
    
    /**
     *
     * @dataProvider fileExceptionProvider
     */
    public function test_file_exists(string $uri, ?string $exception = null) {
        if ($exception) {
            $this->assertFileDoesNotExist($uri);
        } else {
            $this->assertFileExists($uri);
        }
    }
    
    /**
     *
     * @dataProvider fileExceptionProvider
     */
    public function test_file_get_contents(string $uri, ?string $exception = null) {
        if ($exception) {
            $this->expectException($exception);
            file_get_contents($uri);
        } else {
            $this->assertThat(file_get_contents($uri), new LogicalNot(new IsEqual('')));
        }
    }
    
    public function fileExceptionProvider(): iterable {
        yield 'farah://slothsoft@farah/ does exist' => [
            'farah://slothsoft@farah/'
        ];
        
        yield 'farah://slothsoft@farah does exist' => [
            'farah://slothsoft@farah'
        ];
        
        yield 'farah://slothsoft@farah/missing does not exist' => [
            'farah://slothsoft@farah/missing',
            AssetPathNotFoundException::class
        ];
        
        yield 'farah://slothsoft@farah-missing does not exist' => [
            'farah://slothsoft@farah-missing',
            ModuleNotFoundException::class
        ];
        
        yield 'farah://slothsoft@ does not exist' => [
            'farah://slothsoft@',
            Error::class
        ];
        
        yield 'farah://slothsoft-missing does not exist' => [
            'farah://slothsoft-missing',
            IncompleteUrlException::class
        ];
    }
}
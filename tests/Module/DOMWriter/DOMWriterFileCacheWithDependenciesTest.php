<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use DOMDocument;
use SplFileInfo;

/**
 * DOMWriterFileCacheWithDependenciesTest
 *
 * @see DOMWriterFileCacheWithDependencies
 */
final class DOMWriterFileCacheWithDependenciesTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DOMWriterFileCacheWithDependencies::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\DOMWriterFileCacheWithDependencies'!");
    }
    
    private string $xml = <<<EOT
<xml/>
EOT;
    
    private DOMDocument $document;
    
    private DOMWriterInterface $writer;
    
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        
        // FileSystem::removeDir(ServerEnvironment::getCacheDirectory(), true);
    }
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->document = new DOMDocument();
        $this->document->loadXML($this->xml);
        
        $this->writer = new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            return $this->document;
        });
    }
    
    private function createFile(string $method): SplFileInfo {
        return FileInfoFactory::createFromPath(ServerEnvironment::getCacheDirectory() . DIRECTORY_SEPARATOR . md5($method));
    }
    
    public function test_toFile_exists(): void {
        $file = $this->createFile(__METHOD__);
        
        $sut = new DOMWriterFileCacheWithDependencies($this->writer, $file);
        
        $file = $sut->toFile();
        
        $this->assertFileExists((string) $file);
    }
    
    public function test_toFile_contents(): void {
        $file = $this->createFile(__METHOD__);
        
        $sut = new DOMWriterFileCacheWithDependencies($this->writer, $file);
        
        $file = $sut->toFile();
        $expected = $this->writer->toDocument()->saveXML();
        
        $this->assertThat(file_get_contents((string) $file), new IsEqual($expected));
    }
    
    public function test_toDocument(): void {
        $file = $this->createFile(__METHOD__);
        
        $sut = new DOMWriterFileCacheWithDependencies($this->writer, $file);
        
        $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($this->document));
    }
    
    public function test_toDocument_caches(): void {
        $file = $this->createFile(__METHOD__);
        $cacheFile = FileInfoFactory::createTempFile();
        file_put_contents((string) $cacheFile, '');
        
        $sut = new DOMWriterFileCacheWithDependencies($this->writer, $file, (string) $cacheFile);
        $expected = $sut->toDocument();
        
        for ($i = 0; $i < 3; $i ++) {
            $writer = new DOMWriterFromDocumentDelegate(function () use ($i): DOMDocument {
                $document = new DOMDocument();
                $document->loadXML("<should-not-see-me-$i/>");
                return $document;
            });
            
            $sut = new DOMWriterFileCacheWithDependencies($writer, $file, (string) $cacheFile);
            $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($expected));
        }
    }
    
    public function test_toDocument_invalidates(): void {
        $file = $this->createFile(__METHOD__);
        $cacheFile = FileInfoFactory::createTempFile();
        file_put_contents((string) $cacheFile, '');
        
        $sut = new DOMWriterFileCacheWithDependencies($this->writer, $file, (string) $cacheFile);
        $sut->toDocument();
        
        $time = time();
        
        for ($i = 0; $i < 3; $i ++) {
            $writer = new DOMWriterFromDocumentDelegate(function () use ($i): DOMDocument {
                $document = new DOMDocument();
                $document->loadXML("<should-see-me-$i/>");
                return $document;
            });
            $expected = $writer->toDocument();
            
            $time += Seconds::MINUTE;
            touch((string) $cacheFile, $time);
            
            $sut = new DOMWriterFileCacheWithDependencies($writer, $file, (string) $cacheFile);
            $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($expected));
        }
    }
}
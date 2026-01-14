<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use DOMDocument;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

/**
 * DOMWriterFileCacheByUrlTest
 *
 * @see DOMWriterFileCacheByUrl
 */
final class DOMWriterFileCacheByUrlTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DOMWriterFileCacheByUrl::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\DOMWriterFileCacheByUrl'!");
    }
    
    private string $xml = <<<EOT
<xml/>
EOT;
    
    private DOMDocument $document;
    
    private DOMWriterInterface $writer;
    
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        
        FileSystem::removeDir(ServerEnvironment::getCacheDirectory(), true);
    }
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->document = new DOMDocument();
        $this->document->loadXML($this->xml);
        
        $this->writer = new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            return $this->document;
        });
    }
    
    private function createUrl(string $method): FarahUrl {
        return FarahUrl::createFromReference('farah://slothsoft@test/' . strtr($method, [
            '\\' => FarahUrlPath::SEPARATOR,
            '::' => '?'
        ]));
    }
    
    /**
     *
     * @dataProvider pathProvider
     */
    public function test_toFile_path(string $url, string $path): void {
        $url = $this->createUrl($url);
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer);
        
        $file = $sut->toFile();
        
        $this->assertThat((string) $file, new IsEqual($path));
    }
    
    public function pathProvider(): iterable {
        yield 'path' => [
            '/directory/path?key=value&b=c#hashtag',
            implode(DIRECTORY_SEPARATOR, [
                ServerEnvironment::getCacheDirectory(),
                'slothsoft@test',
                'directory-path',
                'b=c&key=value',
                'hashtag.xml'
            ])
        ];
        
        yield 'url' => [
            '?' . FarahUrlArguments::createFromValueList([
                'url' => 'farah://slothsoft@farah/phpinfo?key=value'
            ]),
            implode(DIRECTORY_SEPARATOR, [
                ServerEnvironment::getCacheDirectory(),
                'slothsoft@test',
                'url=' . urlencode('farah://slothsoft@farah/phpinfo?key=value'),
                'index.xml'
            ])
        ];
    }
    
    public function test_toFile_exists(): void {
        $url = $this->createUrl(__METHOD__);
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer);
        
        $file = $sut->toFile();
        
        $this->assertFileExists((string) $file);
    }
    
    public function test_toFile_contents(): void {
        $url = $this->createUrl(__METHOD__);
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer);
        
        $file = $sut->toFile();
        $expected = $this->writer->toDocument()->saveXML();
        
        $this->assertThat(file_get_contents((string) $file), new IsEqual($expected));
    }
    
    public function test_toDocument(): void {
        $url = $this->createUrl(__METHOD__);
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer);
        
        $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($this->document));
    }
    
    public function test_toDocument_caches(): void {
        $url = $this->createUrl(__METHOD__);
        $cacheFile = FileInfoFactory::createTempFile();
        file_put_contents((string) $cacheFile, '');
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer, (string) $cacheFile);
        $expected = $sut->toDocument();
        
        for ($i = 0; $i < 3; $i ++) {
            $writer = new DOMWriterFromDocumentDelegate(function () use ($i): DOMDocument {
                $document = new DOMDocument();
                $document->loadXML("<should-not-see-me-$i/>");
                return $document;
            });
            
            $sut = new DOMWriterFileCacheByUrl($url, $writer, (string) $cacheFile);
            $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($expected));
        }
    }
    
    public function test_toDocument_invalidates(): void {
        $url = $this->createUrl(__METHOD__);
        $cacheFile = FileInfoFactory::createTempFile();
        file_put_contents((string) $cacheFile, '');
        
        $sut = new DOMWriterFileCacheByUrl($url, $this->writer, (string) $cacheFile);
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
            
            $sut = new DOMWriterFileCacheByUrl($url, $writer, (string) $cacheFile);
            $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($expected));
        }
    }
}
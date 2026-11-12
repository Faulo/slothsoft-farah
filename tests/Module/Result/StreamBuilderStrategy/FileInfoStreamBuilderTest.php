<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use SplFileInfo;

/**
 * FileInfoStreamBuilderTest
 *
 * @see FileInfoStreamBuilder
 */
class FileInfoStreamBuilderTest extends TestCase {
    
    public function testMissingOptionalFileKeepsEmptyMetadata(): void {
        $builder = new FileInfoStreamBuilder(new SplFileInfo(temp_file(__METHOD__)));
        $context = $this->createMock(ResultInterface::class);
        $this->assertSame([], $builder->buildStreamFileStatistics($context));
        $this->assertSame('', $builder->buildStreamHash($context));
        $this->assertFalse($builder->buildStreamIsBufferable($context));
    }
    
    public function testHashReadFailureThrows(): void {
        $path = temp_file(__METHOD__);
        file_put_contents($path, 'data');
        $file = $this->createMock(SplFileInfo::class);
        $calls = 0;
        $file->method('__toString')->willReturnCallback(function () use ($path, &$calls): string {
            // The file vanishes after the existence check, just before hashing.
            if (++$calls === 2) {
                unlink($path);
                clearstatcache(true, $path);
            }
            return $path;
        });
        $builder = new FileInfoStreamBuilder($file);
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Failed to hash asset file');
            $builder->buildStreamHash($this->createMock(ResultInterface::class));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FileInfoStreamBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder'!");
    }
    
    private FileInfo $file;
    
    private function createSuT(string $content, string $name): FileInfoStreamBuilder {
        $this->file = FileInfoFactory::createFromString($content);
        
        return new FileInfoStreamBuilder($this->file, $name);
    }
    
    public function test_buildFileWriter(): void {
        $expected = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildFileWriter($context);
        
        $this->assertThat($actual, new IsIdentical($sut));
    }
    
    public function test_buildChunkWriter(): void {
        $expected = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = implode('', iterator_to_array($sut->buildChunkWriter($context)->toChunks(), false));
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildDOMWriter(): void {
        $expected = '<xml>test content</xml>';
        $name = 'test.xml';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        $context->method('createUrl')->willReturn(FarahUrl::createFromReference('farah://test@test'));
        
        $actual = $sut->buildDOMWriter($context)->toDocument();
        
        $this->assertThat($actual, new DOMNodeEqualTo((new DOMHelper())->parse($expected)));
    }
    
    public function test_buildStreamFileName(): void {
        $content = 'test content';
        $expected = 'test';
        
        $sut = $this->createSuT($content, $expected);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStreamFileName($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildStreamCharset(): void {
        $content = 'test content';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamCharset($context), new IsIdentical('UTF-8'));
    }
    
    public function test_buildStreamFileStatistics(): void {
        $content = 'test content';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamFileStatistics($context), new IsIdentical(stat($this->file->getRealPath())));
    }
    
    public function test_buildStreamMimeType(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamMimeType($context), new IsIdentical('text/plain'));
    }
    
    public function test_buildStreamIsBufferable(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamIsBufferable($context), new IsTrue());
    }
    
    public function test_buildStreamHash(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamHash($context), new IsIdentical(md5_file($this->file->getRealPath())));
    }
    
    public function test_buildStringWriter(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStringWriter($context)->toString();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_buildStreamWriter(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = (string) $sut->buildStreamWriter($context)->toStream();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_toFile(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        
        $actual = $sut->toFile();
        
        $this->assertThat($actual, new IsIdentical($this->file));
    }
}

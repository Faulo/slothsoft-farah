<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsTrue;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * FileInfoStreamBuilderTest
 *
 * @see FileInfoStreamBuilder
 */
class FileInfoStreamBuilderTest extends TestCase {
    
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
        
        $actual = '';
        foreach ($sut->buildChunkWriter($context)->toChunks() as $chunk) {
            $actual .= $chunk;
        }
        
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
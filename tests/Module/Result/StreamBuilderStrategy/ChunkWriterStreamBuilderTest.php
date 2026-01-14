<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsIdentical;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * ChunkWriterStreamBuilderTest
 *
 * @see ChunkWriterStreamBuilder
 */
final class ChunkWriterStreamBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ChunkWriterStreamBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ChunkWriterStreamBuilder'!");
    }
    
    private function createSuT(string $content, string $name, bool $isBufferable = true): ChunkWriterStreamBuilder {
        $writer = new ChunkWriterFromChunksDelegate(function () use ($content): iterable {
            yield $content;
        });
        
        return new ChunkWriterStreamBuilder($writer, $name, $isBufferable);
    }
    
    public function test_buildFileWriter(): void {
        $expected = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = file_get_contents($sut->buildFileWriter($context)
            ->toFile()
            ->getRealPath());
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildFileWriter_usesChunkWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildFileWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildChunkWriter(): void {
        $expected = $this->createMock(ChunkWriterInterface::class);
        
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildChunkWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildChunkWriter_usesChunkWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildChunkWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildDOMWriter(): void {
        $expected = '<xml>test content</xml>';
        $name = 'test.xml';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildDOMWriter($context)->toDocument();
        
        $this->assertThat($actual, new DOMNodeEqualTo((new DOMHelper())->parse($expected)));
    }
    
    public function test_buildDOMWriter_usesChunkWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildDOMWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
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
        
        $this->assertThat($sut->buildStreamFileStatistics($context), new IsIdentical([]));
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
        
        $sut = $this->createSuT($content, $name, false);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamIsBufferable($context), new IsFalse());
    }
    
    public function test_buildStreamHash(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamHash($context), new IsIdentical(''));
    }
    
    public function test_buildStringWriter(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStringWriter($context)->toString();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_buildStringWriter_usesChunkWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildStringWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildStreamWriter(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = (string) $sut->buildStreamWriter($context)->toStream();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_buildStreamWriter_usesChunkWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildStreamWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_toChunks(): void {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        
        $actual = '';
        foreach ($sut->toChunks() as $chunk) {
            $actual .= $chunk;
        }
        
        $this->assertThat($actual, new IsIdentical($content));
    }
}
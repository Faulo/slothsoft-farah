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
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * ChunkWriterStreamBuilderTest
 *
 * @see ChunkWriterStreamBuilder
 */
final class ChunkWriterStreamBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ChunkWriterStreamBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ChunkWriterStreamBuilder'!");
    }
    
    public function test_read_once(): void {
        $ref = 'farah://slothsoft@farah/phpinfo';
        
        ob_start();
        phpinfo();
        $expected = ob_get_contents();
        ob_end_clean();
        
        $expected = '<pre>' . htmlentities($expected, ENT_XML1 | ENT_DISALLOWED, 'UTF-8') . '</pre>';
        
        $actual = file_get_contents($ref);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    public function test_read_twice(): void {
        $ref = 'farah://slothsoft@farah/phpinfo';
        
        $expected = file_get_contents($ref);
        $actual = file_get_contents($ref);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    private function createSuT(string $content, string $name, bool $isBufferable = true): ChunkWriterStreamBuilder {
        $writer = new ChunkWriterFromChunksDelegate(function () use ($content): iterable {
            yield $content;
        });
        
        return new ChunkWriterStreamBuilder($writer, $name, $isBufferable);
    }
    
    public function test_buildFileWriter() {
        $expected = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = file_get_contents($sut->buildFileWriter($context)
            ->toFile()
            ->getRealPath());
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildChunkWriter() {
        $expected = $this->createMock(ChunkWriterInterface::class);
        
        $sut = new ChunkWriterStreamBuilder($expected, 'test');
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildChunkWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildDOMWriter() {
        $expected = '<xml>test content</xml>';
        $name = 'test.xml';
        
        $sut = $this->createSuT($expected, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildDOMWriter($context)->toDocument();
        
        $this->assertThat($actual, new DOMNodeEqualTo((new DOMHelper())->parse($expected)));
    }
    
    public function test_buildStreamFileName() {
        $content = 'test content';
        $expected = 'test';
        
        $sut = $this->createSuT($content, $expected);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStreamFileName($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildStreamCharset() {
        $content = 'test content';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamCharset($context), new IsIdentical('UTF-8'));
    }
    
    public function test_buildStreamFileStatistics() {
        $content = 'test content';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamFileStatistics($context), new IsIdentical([]));
    }
    
    public function test_buildStreamMimeType() {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamMimeType($context), new IsIdentical('text/plain'));
    }
    
    public function test_buildStreamIsBufferable() {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name, false);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamIsBufferable($context), new IsFalse());
    }
    
    public function test_buildStreamHash() {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamHash($context), new IsIdentical(''));
    }
    
    public function test_buildStringWriter() {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStringWriter($context)->toString();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_buildStreamWriter() {
        $content = 'test content';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = (string) $sut->buildStreamWriter($context)->toStream();
        
        $this->assertThat($actual, new IsIdentical($content));
    }
    
    public function test_toChunks() {
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
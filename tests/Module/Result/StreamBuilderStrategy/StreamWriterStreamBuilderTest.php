<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsIdentical;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * StreamWriterStreamBuilderTest
 *
 * @see StreamWriterStreamBuilder
 */
final class StreamWriterStreamBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(StreamWriterStreamBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamWriterStreamBuilder'!");
    }
    
    public function test_buildChunkWriter_usesStreamWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new StreamWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildChunkWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildDOMWriter_usesStreamWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new StreamWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildDOMWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildFileWriter_usesStreamWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new StreamWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildFileWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildStreamWriter_usesStreamWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new StreamWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildStreamWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
    
    public function test_buildStringWriter_usesStreamWriter(): void {
        $expected = new AllWriterMock();
        $context = $this->createMock(ResultInterface::class);
        $sut = new StreamWriterStreamBuilder($expected, 'test');
        
        $actual = $sut->buildStringWriter($context);
        
        $this->assertThat($actual, new IsIdentical($expected));
    }
}
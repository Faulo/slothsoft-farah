<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsTrue;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\Module\Result\ResultInterface;
use DOMDocument;

/**
 * DOMWriterStreamBuilderTest
 *
 * @see DOMWriterStreamBuilder
 */
class DOMWriterStreamBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DOMWriterStreamBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder'!");
    }
    
    private DOMDocument $document;
    
    private function createSuT(string $content, string $name): DOMWriterStreamBuilder {
        $this->document = new \DOMDocument();
        $this->document->loadXML($content);
        
        $writer = new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            return $this->document;
        });
        
        return new DOMWriterStreamBuilder($writer, $name);
    }
    
    public function test_buildFileWriter() {
        $content = '<xml>test content</xml>';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildFileWriter($context);
        
        $this->assertThat(file_get_contents($actual->toFile()
            ->getRealPath()), new IsIdentical($this->document->saveXML()));
    }
    
    public function test_buildChunkWriter() {
        $content = '<xml>test content</xml>';
        $name = 'test.txt';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = '';
        foreach ($sut->buildChunkWriter($context)->toChunks() as $chunk) {
            $actual .= $chunk;
        }
        
        $this->assertThat($actual, new IsIdentical($this->document->saveXML()));
    }
    
    public function test_buildDOMWriter() {
        $content = '<xml>test content</xml>';
        $name = 'test.xml';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildDOMWriter($context)->toDocument();
        
        $this->assertThat($actual, new IsIdentical($this->document));
    }
    
    /**
     *
     * @dataProvider namespaceExamples
     */
    public function test_buildStreamFileName(string $content, string $extension, string $mimeType) {
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStreamFileName($context);
        
        $this->assertThat($actual, new IsIdentical("$name.$extension"));
    }
    
    public function namespaceExamples(): iterable {
        yield 'xml' => [
            '<xml>test content</xml>',
            'xml',
            'application/xml'
        ];
        yield 'html' => [
            '<xml xmlns="http://www.w3.org/1999/xhtml">test content</xml>',
            'xhtml',
            'application/xhtml+xml'
        ];
        yield 'svg' => [
            '<xml xmlns="http://www.w3.org/2000/svg">test content</xml>',
            'svg',
            'image/svg+xml'
        ];
        yield 'xslt' => [
            '<xml xmlns="http://www.w3.org/1999/XSL/Transform">test content</xml>',
            'xsl',
            'application/xslt+xml'
        ];
        yield 'xsd' => [
            '<xml xmlns="http://www.w3.org/2001/XMLSchema">test content</xml>',
            'xsd',
            'application/x-xsd+xml'
        ];
    }
    
    public function test_buildStreamCharset() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamCharset($context), new IsIdentical('UTF-8'));
    }
    
    public function test_buildStreamFileStatistics() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamFileStatistics($context), new IsIdentical([]));
    }
    
    /**
     *
     * @dataProvider namespaceExamples
     */
    public function test_buildStreamMimeType(string $content, string $extension, string $mimeType) {
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamMimeType($context), new IsIdentical($mimeType));
    }
    
    public function test_buildStreamIsBufferable() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamIsBufferable($context), new IsTrue());
    }
    
    public function test_buildStreamHash() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $this->assertThat($sut->buildStreamHash($context), new IsIdentical(md5($this->document->saveXML())));
    }
    
    public function test_buildStringWriter() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = $sut->buildStringWriter($context)->toString();
        
        $this->assertThat($actual, new IsIdentical($this->document->saveXML()));
    }
    
    public function test_buildStreamWriter() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        $context = $this->createMock(ResultInterface::class);
        
        $actual = (string) $sut->buildStreamWriter($context)->toStream();
        
        $this->assertThat($actual, new IsIdentical($this->document->saveXML()));
    }
    
    public function test_toDocument() {
        $content = '<xml>test content</xml>';
        $name = 'test';
        
        $sut = $this->createSuT($content, $name);
        
        $actual = $sut->toDocument();
        
        $this->assertThat($actual, new IsIdentical($this->document));
    }
}
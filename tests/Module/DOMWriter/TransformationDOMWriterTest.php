<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * TransformationDOMWriterTest
 *
 * @see TransformationDOMWriter
 */
final class TransformationDOMWriterTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(TransformationDOMWriter::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriter'!");
    }
    
    public function test_toDocument() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/html');
        
        $sut = new TransformationDOMWriter(Module::resolveToDOMWriter($source), Module::resolveToDOMWriter($template));
        
        $actual = $sut->toDocument();
        
        $this->assertThat($actual, new IsInstanceOf(DOMDocument::class));
    }
    
    public function test_toElement() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/html');
        
        $sut = new TransformationDOMWriter(Module::resolveToDOMWriter($source), Module::resolveToDOMWriter($template));
        
        $actual = $sut->toElement(new DOMDocument());
        
        $this->assertThat($actual, new IsInstanceOf(DOMElement::class));
    }
    
    public function test_emptyTransformationException() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/module');
        
        $sut = new TransformationDOMWriter(Module::resolveToDOMWriter($source), Module::resolveToDOMWriter($template));
        
        $this->expectException(EmptyTransformationException::class);
        
        $sut->toDocument();
    }
}
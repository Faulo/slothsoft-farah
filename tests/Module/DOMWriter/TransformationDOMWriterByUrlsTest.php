<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;
use Slothsoft\Farah\Exception\EmptyTransformationException;

/**
 * TransformationDOMWriterByUrlsTest
 *
 * @see TransformationDOMWriterByUrls
 */
final class TransformationDOMWriterByUrlsTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(TransformationDOMWriterByUrls::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriterByUrls'!");
    }
    
    public function test_toDocument() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/html');
        
        $sut = new TransformationDOMWriterByUrls($source, $template);
        
        $actual = $sut->toDocument();
        
        $this->assertThat($actual, new IsInstanceOf(DOMDocument::class));
    }
    
    public function test_toElement() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/html');
        
        $sut = new TransformationDOMWriterByUrls($source, $template);
        
        $actual = $sut->toElement(new DOMDocument());
        
        $this->assertThat($actual, new IsInstanceOf(DOMElement::class));
    }
    
    public function test_emptyTransformationException() {
        $source = FarahUrl::createFromReference('farah://slothsoft@farah/phpinfo');
        $template = FarahUrl::createFromReference('farah://slothsoft@farah/xsl/module');
        
        $sut = new TransformationDOMWriterByUrls($source, $template);
        
        $this->expectException(EmptyTransformationException::class);
        
        $sut->toDocument();
    }
}
<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use DOMDocument;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * FarahDecoratorTest
 *
 * @see FarahDecorator
 */
class FarahDecoratorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FarahDecorator::class), "Failed to load class 'Slothsoft\Farah\LinkDecorator\FarahDecorator'!");
    }
    
    /**
     *
     * @dataProvider provideLinkedStylesheets
     */
    public function test_linkStylesheets(string $input, string $url, string $expected) {
        $inputDocument = new DOMDocument();
        $inputDocument->loadXML($input);
        
        $sut = new FarahDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkStylesheets(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedStylesheets(): iterable {
        yield 'data' => [
            <<<EOT
<data/>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data><link-stylesheet xmlns="http://schema.slothsoft.net/farah/module" ref="farah://slothsoft@test/path" /></data>
EOT
        ];
    }
    
    /**
     *
     * @dataProvider provideLinkedScripts
     */
    public function test_linkScripts(string $input, string $url, string $expected) {
        $inputDocument = new DOMDocument();
        $inputDocument->loadXML($input);
        
        $sut = new FarahDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkScripts(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedScripts(): iterable {
        yield 'data' => [
            <<<EOT
<data/>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data><link-script xmlns="http://schema.slothsoft.net/farah/module" ref="farah://slothsoft@test/path" /></data>
EOT
        ];
    }
    
    /**
     *
     * @dataProvider provideLinkedModules
     */
    public function test_linkModules(string $input, string $url, string $expected) {
        $inputDocument = new DOMDocument();
        $inputDocument->loadXML($input);
        
        $sut = new FarahDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkModules(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedModules(): iterable {
        yield 'data' => [
            <<<EOT
<data/>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data><link-module xmlns="http://schema.slothsoft.net/farah/module" ref="farah://slothsoft@test/path" /></data>
EOT
        ];
    }
    
    /**
     *
     * @dataProvider provideLinkedContents
     */
    public function test_linkContents(string $input, string $url, string $expected) {
        $inputDocument = new DOMDocument();
        $inputDocument->loadXML($input);
        
        $sut = new FarahDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkContents(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedContents(): iterable {
        yield 'data' => [
            <<<EOT
<data/>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data><link-content xmlns="http://schema.slothsoft.net/farah/module" ref="farah://slothsoft@test/path" /></data>
EOT
        ];
    }
}
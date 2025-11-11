<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;

/**
 * SvgDecoratorTest
 *
 * @see SvgDecorator
 */
class SvgDecoratorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SvgDecorator::class), "Failed to load class 'Slothsoft\Farah\LinkDecorator\SvgDecorator'!");
    }
    
    /**
     *
     * @dataProvider provideLinkedStylesheets
     */
    public function test_linkStylesheets(string $input, string $url, string $expected) {
        $inputDocument = new DOMDocument();
        $inputDocument->loadXML($input);
        
        $sut = new SvgDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkStylesheets(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedStylesheets(): iterable {
        yield 'data' => [
            <<<EOT
<data>
    <element />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
            
<data>
    <element />
    <link rel="stylesheet" type="text/css" xmlns="http://www.w3.org/1999/xhtml" href="/slothsoft@test/path" />
</data>
EOT
        ];
        
        yield 'head' => [
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg" />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
            
<data>
    <defs xmlns="http://www.w3.org/2000/svg">
        <link rel="stylesheet" type="text/css" xmlns="http://www.w3.org/1999/xhtml" href="/slothsoft@test/path" />
    </defs>
</data>
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
        
        $sut = new SvgDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkScripts(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedScripts(): iterable {
        yield 'data' => [
            <<<EOT
<data>
    <element />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
            
<data>
    <element />
    <script type="application/javascript" xmlns="http://www.w3.org/2000/svg" href="/slothsoft@test/path" />
</data>
EOT
        ];
        
        yield 'head' => [
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg" />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
            
<data>
    <defs xmlns="http://www.w3.org/2000/svg">
        <script type="application/javascript" xmlns="http://www.w3.org/2000/svg" href="/slothsoft@test/path" />
    </defs>
</data>
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
        
        $sut = new SvgDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkModules(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedModules(): iterable {
        yield 'data' => [
            <<<EOT
<data>
    <element />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data>
    <element />
    <script type="module" xmlns="http://www.w3.org/2000/svg" href="/slothsoft@test/path" />
</data>
EOT
        ];
        
        yield 'head' => [
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg" />
</data>
EOT,
            'farah://slothsoft@test/path',
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg">
        <script type="module" xmlns="http://www.w3.org/2000/svg" href="/slothsoft@test/path" />
    </defs>
</data>
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
        
        $sut = new SvgDecorator();
        $sut->setTarget($inputDocument);
        
        $sut->linkContents(FarahUrl::createFromReference($url));
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->loadXML($expected);
        
        $this->assertThat($inputDocument, new DOMNodeEqualTo($expectedDocument));
    }
    
    public function provideLinkedContents(): iterable {
        yield 'data' => [
            <<<EOT
<data>
    <element />
</data>
EOT,
            'farah://slothsoft@farah/example-domain',
            <<<EOT
<data>
    <element />
    <template xmlns="http://www.w3.org/1999/xhtml" data-url="farah://slothsoft@farah/example-domain">
        <domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" xmlns:sfm="http://schema.slothsoft.net/farah/module" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.slothsoft.net/farah/sitemap http://schema.slothsoft.net/farah/sitemap/1.1.xsd" name="example-domain.net" vendor="slothsoft" module="farah" ref="/" status-active="" status-public="" sfd:languages="en-us" version="1.1">
            <file name="sitemap" ref="/sitemap-generator" status-active="" />
        </domain>
    </template>
</data>
EOT
        ];
        
        yield 'head' => [
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg" />
</data>
EOT,
            'farah://slothsoft@farah/example-domain',
            <<<EOT
<data>
    <defs xmlns="http://www.w3.org/2000/svg">
        <template xmlns="http://www.w3.org/1999/xhtml" data-url="farah://slothsoft@farah/example-domain">
            <domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" xmlns:sfm="http://schema.slothsoft.net/farah/module" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.slothsoft.net/farah/sitemap http://schema.slothsoft.net/farah/sitemap/1.1.xsd" name="example-domain.net" vendor="slothsoft" module="farah" ref="/" status-active="" status-public="" sfd:languages="en-us" version="1.1">
                <file name="sitemap" ref="/sitemap-generator" status-active="" />
            </domain>
        </template>
    </defs>
</data>
EOT
        ];
    }
}
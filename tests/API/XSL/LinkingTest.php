<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use DOMDocument;

final class LinkingTest extends TestCase {
    
    private DOMDocument $linkingDocument;
    
    public function setUp(): void {
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
        
        $this->linkingDocument = DOMHelper::loadDocument('farah://slothsoft@test-module/tests/linking');
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_linkingResult(string $ns, string $tagName, string $expectedXml): void {
        $actual = $this->linkingDocument->createDocumentFragment();
        foreach ($this->linkingDocument->getElementsByTagNameNS($ns, $tagName) as $node) {
            $actual->appendChild($node->cloneNode(false));
        }
        
        $dom = new DOMHelper();
        $expected = $dom->parse($expectedXml);
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    public function exampleProvider(): iterable {
        yield 'only one link' => [
            DOMHelper::NS_HTML,
            'link',
            <<<EOT
<link xmlns="http://www.w3.org/1999/xhtml" rel="stylesheet" type="text/css" href="/slothsoft@test-module/test" />
EOT
        ];
        
        yield 'only one script and module' => [
            DOMHelper::NS_HTML,
            'script',
            <<<EOT
<script xmlns="http://www.w3.org/1999/xhtml" type="application/javascript" src="/slothsoft@test-module/test" defer="defer" />
<script xmlns="http://www.w3.org/1999/xhtml" type="module" src="/slothsoft@test-module/test" />
EOT
        ];
        yield 'only one template' => [
            DOMHelper::NS_HTML,
            'template',
            <<<EOT
<template xmlns="http://www.w3.org/1999/xhtml" data-url="farah://slothsoft@test-module/test" />
EOT
        ];
    }
}
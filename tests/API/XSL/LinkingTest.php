<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;

final class LinkingTest extends TestCase {
    
    public function setUp(): void {
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), __DIR__ . '/../../../test-files/test-module');
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_linkingResult(string $ns, string $tagName, string $expectedXml): void {
        $linkingDocument = DOMHelper::loadDocument('farah://slothsoft@test-module/tests/linking');
        
        $actual = $linkingDocument->createDocumentFragment();
        foreach ($linkingDocument->getElementsByTagNameNS($ns, $tagName) as $node) {
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
            /** @lang TEXT */
            <<<EOT
<link xmlns="http://www.w3.org/1999/xhtml" rel="stylesheet" type="text/css" href="/slothsoft@test-module/test" />
EOT
        ];
        
        yield 'only one script and module' => [
            DOMHelper::NS_HTML,
            'script',
            /** @lang TEXT */
            <<<EOT
<script xmlns="http://www.w3.org/1999/xhtml" type="application/javascript" src="/slothsoft@test-module/test" defer="defer" />
<script xmlns="http://www.w3.org/1999/xhtml" type="module" src="/slothsoft@test-module/test" async="async" />
EOT
        ];
        
        yield 'only one template' => [
            DOMHelper::NS_HTML,
            'template',
            /** @lang TEXT */
            <<<EOT
<template xmlns="http://www.w3.org/1999/xhtml" data-url="farah://slothsoft@test-module/test" xml:base="farah://slothsoft@test-module/test" />
EOT
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_canLinkDefaultContent(): void {
        $linkingDocument = DOMHelper::loadDocument('farah://slothsoft@test-module/tests/linking-data');
        
        $actual = $linkingDocument->createDocumentFragment();
        foreach ($linkingDocument->getElementsByTagNameNS(DOMHelper::NS_HTML, 'template') as $node) {
            $actual->appendChild($node->cloneNode(true));
        }
        
        $dom = new DOMHelper();
        $expected = $dom->parse(
            /** @lang TEXT */
            <<<EOT
<html:template xmlns:html="http://www.w3.org/1999/xhtml" data-url="farah://slothsoft@test-module/data?includes=embed" xml:base="farah://slothsoft@test-module/data">
    <data />
</html:template>
EOT
        );
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }

    /**
     *
     * @runInSeparateProcess
     */
    public function test_canSerializeLinkedModulesAsHTML5(): void {
        $result = Module::resolveToResult(FarahUrl::createFromReference('farah://slothsoft@test-module/tests/linking#html'));

        $this->assertSame('text/html', $result->lookupMimeType());
        $this->assertSame('transformation.html', $result->lookupFileName());

        $html = $result->lookupStringWriter()->toString();
        $modulePath = sprintf('/%s/%s', 'slothsoft@test-module', 'test');
        $expectedScript = '<' . sprintf('script src="%s" type="module" async="async"></script>', $modulePath);
        $this->assertStringContainsString($expectedScript, $html);
        $this->assertStringNotContainsString('default:', $html);
    }
}

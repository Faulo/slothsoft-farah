<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\Module\Result\ResultInterface;

final class HTML5DOMWriterStreamBuilderTest extends TestCase {

    private function createSuT(): HTML5DOMWriterStreamBuilder {
        $document = new DOMDocument();
        $document->loadXML(<<<'SOURCE'
<?xml version="1.0" encoding="UTF-8"?>
<html:html xmlns:html="http://www.w3.org/1999/xhtml">
    <html:head xmlns:unused="urn:unused">
        <html:meta charset="UTF-8" />
        <html:script type="module" />
    </html:head>
    <html:body xmlns:a="urn:example:a" xmlns:b="urn:example:b" xmlns:component="urn:example:component" xml:lang="de" lang="en" a:title="ignored" title="plain">
        <html:input name="test" />
        <html:div a:data-test="projected" b:title="flattened" />
        <html:p xml:lang="de">Sprache</html:p>
        <component:widget component:name="example"><component:script>if (a &lt; b) run();</component:script></component:widget>
        <svg:svg xmlns:svg="http://www.w3.org/2000/svg" xmlns:arbitrary="http://www.w3.org/1999/xlink" arbitrary:href="#icon" arbitrary:title=""><svg:path /></svg:svg>
        <math:math xmlns:math="http://www.w3.org/1998/Math/MathML"><math:mi a:definitionURL="projected" /></math:math>
    </html:body>
</html:html>
SOURCE
        );

        return new HTML5DOMWriterStreamBuilder(new DOMWriterFromDocumentDelegate(function () use ($document): DOMDocument {
            return $document;
        }), 'page');
    }

    public function test_buildStringWriter_serializesHTML5(): void {
        $context = $this->createMock(ResultInterface::class);
        $actual = $this->createSuT()->buildStringWriter($context)->toString();

        $this->assertStringStartsWith('<!DOCTYPE html>', $actual);
        $this->assertStringNotContainsString('<?xml', $actual);
        $this->assertStringNotContainsString('html:', $actual);
        $this->assertStringNotContainsString('xmlns', $actual);
        $this->assertStringNotContainsString('xml:lang', $actual);
        $this->assertStringNotContainsString('a:', $actual);
        $this->assertStringNotContainsString('b:', $actual);
        $this->assertStringNotContainsString('arbitrary:', $actual);
        $this->assertStringNotContainsString('component:', $actual);
        $this->assertStringContainsString('<meta charset="UTF-8">', $actual);
        $this->assertStringContainsString('<script type="module"></script>', $actual);
        $this->assertStringContainsString('<input name="test">', $actual);
        $this->assertStringContainsString('<body lang="en" title="plain">', $actual);
        $this->assertStringContainsString('<div data-test="projected" title="flattened"></div>', $actual);
        $this->assertStringContainsString('<p lang="de">Sprache</p>', $actual);
        $this->assertStringContainsString('<widget name="example">', $actual);
        $this->assertStringContainsString('if (a ' . '<' . ' b) run();', $actual);
        $this->assertStringContainsString('xlink:href="#icon" xlink:title=""', $actual);
        $this->assertStringContainsString('definitionURL="projected"', $actual);
    }

    public function test_buildStringWriter_doesNotModifySourceDocument(): void {
        $document = $this->createSuT()->toDocument();
        $before = $document->saveXML();

        $context = $this->createMock(ResultInterface::class);
        $sut = new HTML5DOMWriterStreamBuilder(new DOMWriterFromDocumentDelegate(function () use ($document): DOMDocument {
            return $document;
        }));
        $sut->buildStringWriter($context)->toString();

        $this->assertSame($before, $document->saveXML());
    }

    public function test_buildStreamMetadata_describesHTML(): void {
        $context = $this->createMock(ResultInterface::class);
        $sut = $this->createSuT();

        $this->assertSame('text/html', $sut->buildStreamMimeType($context));
        $this->assertSame('UTF-8', $sut->buildStreamCharset($context));
        $this->assertSame('page.html', $sut->buildStreamFileName($context));
        $this->assertSame(md5($sut->buildStringWriter($context)->toString()), $sut->buildStreamHash($context));
    }
}

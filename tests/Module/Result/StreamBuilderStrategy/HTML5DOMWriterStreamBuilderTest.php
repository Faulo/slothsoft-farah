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
        $document->loadXML(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<html:html xmlns:html="http://www.w3.org/1999/xhtml">
    <html:head>
        <html:meta charset="UTF-8" />
        <html:script type="module" />
    </html:head>
    <html:body>
        <html:input name="test" />
        <svg:svg xmlns:svg="http://www.w3.org/2000/svg"><svg:path /></svg:svg>
    </html:body>
</html:html>
XML
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
        $this->assertStringNotContainsString('xmlns:html', $actual);
        $this->assertStringContainsString('<meta charset="UTF-8">', $actual);
        $this->assertStringContainsString('<script type="module"></script>', $actual);
        $this->assertStringContainsString('<input name="test">', $actual);
        $this->assertStringContainsString('<svg><path /></svg>', $actual);
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

<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\HTML5DOMWriterStreamBuilder;
use SplFileInfo;

/**
 * XmlFileResultBuilderTest
 *
 * @see XmlFileResultBuilder
 *
 */
final class XmlFileResultBuilderTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(XmlFileResultBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\XmlFileResultBuilder'!");
    }

    public function test_buildResultStrategies_serializesXhtmlAsHtml(): void {
        $sut = $this->createXhtmlBuilder('farah://slothsoft@test-module/page-xhtml');
        $context = $this->createMock(ExecutableInterface::class);
        $result = $this->createMock(ResultInterface::class);
        $type = Executable::resultIsHtml();
        $streamBuilder = $sut->buildResultStrategies($context, $type)->streamBuilder;
        $body = $streamBuilder->buildStringWriter($result)->toString();

        $this->assertTrue($sut->isDifferentFromDefault($type));
        $this->assertInstanceOf(HTML5DOMWriterStreamBuilder::class, $streamBuilder);
        $this->assertSame('text/html', $streamBuilder->buildStreamMimeType($result));
        $this->assertStringStartsWith('<!DOCTYPE html>', $body);
        $this->assertStringNotContainsString('<?xml', $body);
        $this->assertStringContainsString('<br>', $body);
        $this->assertStringContainsString('<script type="module"></script>', $body);
    }

    public function test_buildResultStrategies_serializesEmbeddedXhtmlAsHtml(): void {
        $sut = $this->createXhtmlBuilder('farah://slothsoft@test-module/page-xhtml?includes=embed');
        $context = $this->createMock(ExecutableInterface::class);
        $result = $this->createMock(ResultInterface::class);
        $streamBuilder = $sut->buildResultStrategies($context, Executable::resultIsHtml())->streamBuilder;
        $body = $streamBuilder->buildStringWriter($result)->toString();

        $this->assertInstanceOf(HTML5DOMWriterStreamBuilder::class, $streamBuilder);
        $this->assertSame('text/html', $streamBuilder->buildStreamMimeType($result));
        $this->assertStringStartsWith('<!DOCTYPE html>', $body);
        $this->assertStringNotContainsString('<?xml', $body);
    }

    public function test_isDifferentFromDefault_doesNotApplyHtmlToGenericXml(): void {
        $sut = new XmlFileResultBuilder(
            FarahUrl::createFromReference('farah://slothsoft@test-module/data'),
            new SplFileInfo(__DIR__ . '/../../../../../test-files/test-module/data.xml'),
            'application/xml'
        );

        $this->assertFalse($sut->isDifferentFromDefault(Executable::resultIsHtml()));
    }

    private function createXhtmlBuilder(string $url): XmlFileResultBuilder {
        return new XmlFileResultBuilder(
            FarahUrl::createFromReference($url),
            new SplFileInfo(__DIR__ . '/../../../../../test-files/test-module/page-xhtml.xhtml'),
            'application/xhtml+xml'
        );
    }
}

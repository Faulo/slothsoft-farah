<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use SplFileInfo;

/**
 * HtmlFileResultBuilderTest
 *
 * @see HtmlFileResultBuilder
 *
 */
final class HtmlFileResultBuilderTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(HtmlFileResultBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\HtmlFileResultBuilder'!");
    }

    public function test_buildResultStrategies_serializesHtmlAsXhtmlForXmlStream(): void {
        $sut = new HtmlFileResultBuilder(
            FarahUrl::createFromReference('farah://slothsoft@test-module/page-html'),
            new SplFileInfo(__DIR__ . '/../../../../../test-files/test-module/page-html.html')
        );
        $context = $this->createMock(ExecutableInterface::class);
        $result = $this->createMock(ResultInterface::class);
        $streamBuilder = $sut->buildResultStrategies($context, Executable::resultIsXml())->streamBuilder;
        $body = $streamBuilder->buildStringWriter($result)->toString();

        $this->assertSame(DOMHelper::NS_HTML, $sut->toDocument()->documentElement->namespaceURI);
        $this->assertSame('application/xhtml+xml', $streamBuilder->buildStreamMimeType($result));
        $this->assertStringStartsWith('<?xml', $body);
        $this->assertStringContainsString('xmlns="http://www.w3.org/1999/xhtml"', $body);
        $this->assertStringContainsString('<br/>', $body);
    }
}

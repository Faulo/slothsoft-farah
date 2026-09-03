<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * DOMWriterResultBuilderTest
 *
 * @see DOMWriterResultBuilder
 */
final class DOMWriterResultBuilderTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DOMWriterResultBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder'!");
    }

    public function test_buildResultStrategies_supportsHTML(): void {
        $document = new DOMDocument();
        $document->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><body /></html>');
        $writer = new DOMWriterFromDocumentDelegate(function () use ($document): DOMDocument {
            return $document;
        });
        $sut = new DOMWriterResultBuilder($writer, 'page');
        $type = Executable::resultIsHtml();
        $context = $this->createMock(ExecutableInterface::class);

        $this->assertTrue($sut->isDifferentFromDefault($type));
        $result = $this->createMock(ResultInterface::class);
        $this->assertSame('text/html', $sut->buildResultStrategies($context, $type)->streamBuilder->buildStreamMimeType($result));
    }
}

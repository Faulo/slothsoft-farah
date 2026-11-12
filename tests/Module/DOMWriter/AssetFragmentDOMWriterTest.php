<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMXPath;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * AssetFragmentDOMWriterTest
 *
 * @see AssetFragmentDOMWriter
 *
 */
final class AssetFragmentDOMWriterTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testBuildsMetadataWithNumericParameters(): void {
        $writer = new AssetFragmentDOMWriter(FarahUrl::createFromReference('farah://test@test/asset?0=value'));
        $document = $writer->toDocument();
        $xpath = new DOMXPath($document);
        $this->assertSame('fragment-info', $document->documentElement?->localName);
        $this->assertSame('value', $xpath->evaluate('string(//*[local-name()="param"][@name="0"]/@value)'));
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AssetFragmentDOMWriter::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\AssetFragmentDOMWriter'!");
    }
}
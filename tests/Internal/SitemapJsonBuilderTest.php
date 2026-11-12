<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;

/**
 * SitemapJsonBuilderTest
 *
 * @see SitemapJsonBuilder
 *
 */
final class SitemapJsonBuilderTest extends TestCase {
    
    /**
     *
     * @test
     */
    /** @return iterable<string, array{string|null, list<string>}> */
    public static function sitemapDocuments(): iterable {
        yield 'empty document' => [null, []];
        yield 'page order' => ['<domain uri="/"><page uri="/first/"/><page uri="/second/"/></domain>', ['/', '/first/', '/second/']];
    }
    
    /**
     * @dataProvider sitemapDocuments
     * @param list<string> $expected
     */
    public function testSerializesSitemapUris(?string $xml, array $expected): void {
        $document = new DOMDocument();
        if ($xml !== null) {
            $document->loadXML($xml);
        }
        $writer = $this->createMock(DOMWriterInterface::class);
        $writer->method('toDocument')->willReturn($document);
        $builder = new SitemapJsonBuilder($writer);
        $this->assertSame($expected, json_decode($builder->toString(), true));
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SitemapJsonBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\SitemapJsonBuilder'!");
    }
}
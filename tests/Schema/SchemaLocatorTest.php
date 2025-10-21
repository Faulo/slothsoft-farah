<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Schema;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use DOMDocument;

/**
 * SchemaLocatorTest
 *
 * @see SchemaLocator
 */
class SchemaLocatorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SchemaLocator::class), "Failed to load class 'Slothsoft\Farah\Schema\SchemaLocator'!");
    }
    
    /**
     *
     * @dataProvider provideXmlWithSchema
     */
    public function test_findSchemaLocation(string $xml, ?string $expected): void {
        $document = new DOMDocument();
        $document->loadXML($xml);
        
        $sut = new SchemaLocator();
        
        $actual = $sut->findSchemaLocation($document);
        
        self::assertThat($actual, new IsEqual($expected));
    }
    
    public static function provideXmlWithSchema(): iterable {
        yield 'nothing' => [
            '<data/>',
            null
        ];
        
        yield 'default version' => [
            '<data xmlns="http://schema.slothsoft.net/farah/sitemap"/>',
            'farah://slothsoft@farah/schema/sitemap/1.0'
        ];
        
        yield 'explicit version' => [
            '<data xmlns="http://schema.slothsoft.net/farah/sitemap" version="0.1"/>',
            'farah://slothsoft@farah/schema/sitemap/0.1'
        ];
        
        yield 'unknown schema' => [
            '<data xmlns="http://www.w3.org/1999/xhtml" version="5.0"/>',
            null
        ];
        
        yield 'schemaLocation' => [
            '<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.6/phpunit.xsd"/>',
            'https://schema.phpunit.de/9.6/phpunit.xsd'
        ];
        
        yield 'schemaLocation and slothsoft namespace' => [
            '<phpunit xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/8.5/phpunit.xsd"/>',
            'https://schema.phpunit.de/8.5/phpunit.xsd'
        ];
        
        yield 'junit special schema' => [
            '<testsuites/>',
            'farah://slothsoft@schema/schema/junit/1.0'
        ];
    }
}
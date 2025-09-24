<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use DOMDocument;

class XSLTest extends TestCase {
    
    public function exampleProvider(): array {
        return [
            'farah://slothsoft@farah/xsl/graph' => [
                'farah://slothsoft@farah/xsl/graph',
                'test-files/graph.xml',
                'test-files/graph.svg'
            ],
            'farah://slothsoft@farah/xsl/xslt sfx:range' => [
                'test-files/xslt.xsl',
                'test-files/xslt-range.xml',
                'test-files/xslt-range.xml'
            ],
            'farah://slothsoft@farah/xsl/xslt sfx:id' => [
                'test-files/xslt.xsl',
                'test-files/xslt-id.xml',
                'test-files/xslt-id.xml'
            ]
        ];
    }
    
    /**
     *
     * @dataProvider exampleProvider
     */
    public function test_xslTemplate(string $templateFile, string $inputFile, string $expectedFile): void {
        $dom = new DOMHelper();
        $actualDocument = $dom->transformToDocument($inputFile, $templateFile);
        $actualDocument->formatOutput = true;
        $actual = $actualDocument->saveXML();
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->load($expectedFile, LIBXML_NOBLANKS);
        $expectedDocument->formatOutput = true;
        $expected = $expectedDocument->saveXML();
        
        $this->assertEquals($expected, $actual);
    }
}
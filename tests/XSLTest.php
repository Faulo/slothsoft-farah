<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;

class XSLTest extends TestCase {
    
    public function exampleProvider(): array {
        return [
            'farah://slothsoft@farah/xsl/graph' => [
                'farah://slothsoft@farah/xsl/graph',
                'test-files/graph.xml',
                'test-files/graph.svg'
            ]
        ];
    }
    
    /**
     *
     * @dataProvider exampleProvider
     */
    public function test_xslTemplate(string $templateUrl, string $inputFile, string $expectedFile): void {
        $dom = new DOMHelper();
        $actualDocument = $dom->transformToDocument($inputFile, $templateUrl);
        $actualDocument->formatOutput = true;
        $actual = $actualDocument->saveXML();
        
        $expectedDocument = $dom->load($expectedFile);
        $expectedDocument->formatOutput = true;
        $expected = $expectedDocument->saveXML();
        
        $this->assertEquals($expected, $actual);
    }
}
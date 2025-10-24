<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Dictionary;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use DOMDocument;

class MiscTransformationsTest extends TestCase {
    
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
            ],
            'farah://slothsoft@farah/xsl/xslt sfx:set-id' => [
                'test-files/xslt.xsl',
                'test-files/xslt-set-id.xml',
                'test-files/xslt-set-id.xml'
            ],
            'farah://slothsoft@farah/xsl/xslt sfx:set-href' => [
                'test-files/xslt.xsl',
                'test-files/xslt-set-href.xml',
                'test-files/xslt-set-href.xml'
            ],
            'farah://slothsoft@farah/xsl/dictionary' => [
                'test-files/dictionary.xsl',
                'test-files/dictionary.xml',
                'test-files/dictionary.xml'
            ]
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_xslTemplate(string $templateFile, string $inputFile, string $expectedFile): void {
        Dictionary::setSupportedLanguages('en-us', 'de-de');
        
        $authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module');
        Module::registerWithXmlManifestAndDefaultAssets($authority, 'test-files/test-module');
        
        $dom = new DOMHelper();
        $actualDocument = $dom->transformToDocument($inputFile, $templateFile);
        
        $expectedDocument = new DOMDocument();
        $expectedDocument->load($expectedFile, LIBXML_NOBLANKS);
        
        $this->assertThat($actualDocument, new DOMNodeEqualTo($expectedDocument));
    }
}
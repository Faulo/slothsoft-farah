<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;

/**
 * FileInfoDOMWriterTest
 *
 * @see EmbedIncludesDOMWriter
 */
class EmbedIncludesDOMWriterTest extends TestCase {
    
    private FarahUrl $base;
    
    public function setUp(): void {
        $this->base = FarahUrl::createFromReference('farah://slothsoft@test-module');
        Module::registerWithXmlManifestAndDefaultAssets($this->base->getAssetAuthority(), __DIR__ . '/../../../test-files/test-module');
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(EmbedIncludesDOMWriter::class), "Failed to load class 'Slothsoft\Farah\Module\DOMWriter\FileInfoDOMWriter'!");
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_toDocument(string $inputXML, string $expectedXML) {
        $delegate = function () use ($inputXML): DOMDocument {
            $document = new DOMDocument();
            $document->loadXML($inputXML);
            return $document;
        };
        
        $source = new DOMWriterFromDocumentDelegate($delegate);
        
        $sut = new EmbedIncludesDOMWriter($source, FarahUrl::createFromReference('farah://test@test'));
        
        $document = new DOMDocument();
        $document->loadXML($expectedXML);
        
        $this->assertThat($sut->toDocument(), new DOMNodeEqualTo($document));
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_toElement(string $inputXML, string $expectedXML) {
        $delegate = function () use ($inputXML): DOMDocument {
            $document = new DOMDocument();
            $document->loadXML($inputXML);
            return $document;
        };
        
        $source = new DOMWriterFromDocumentDelegate($delegate);
        
        $sut = new EmbedIncludesDOMWriter($source, FarahUrl::createFromReference('farah://test@test'));
        
        $document = new DOMDocument();
        $document->loadXML($expectedXML);
        
        $this->assertThat($sut->toElement($document), new DOMNodeEqualTo($document->documentElement));
    }
    
    public function exampleProvider(): iterable {
        yield 'skip includes without href' => [
            <<<EOT
<root>
    <include xmlns="http://www.w3.org/1999/XSL/Transform" />
</root>
EOT,
            <<<EOT
<root>
    <include xmlns="http://www.w3.org/1999/XSL/Transform" />
</root>
EOT
        ];
        
        yield 'import url' => [
            <<<EOT
<root>
    <include xmlns="http://www.w3.org/1999/XSL/Transform" href="farah://slothsoft@test-module/xsl/test" />
</root>
EOT,
            <<<EOT
<root xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="something">
		<something />
	</xsl:template>

	<xsl:template match="nothing" />
</root>
EOT
        ];
        
        yield 'only import url once' => [
            <<<EOT
<root>
    <include xmlns="http://www.w3.org/1999/XSL/Transform" href="farah://slothsoft@test-module/xsl/test" />
    <include xmlns="http://www.w3.org/1999/XSL/Transform" href="farah://slothsoft@test-module/xsl/test" />
    <include xmlns="http://www.w3.org/1999/XSL/Transform" href="farah://slothsoft@test-module/xsl/test" />
</root>
EOT,
            <<<EOT
<root xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="something">
		<something />
	</xsl:template>
            
	<xsl:template match="nothing" />
</root>
EOT
        ];
    }
}
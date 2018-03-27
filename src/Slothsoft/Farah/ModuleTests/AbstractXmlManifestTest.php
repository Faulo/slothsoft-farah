<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\DOMHelper;
use DOMDocument;

abstract class AbstractXmlManifestTest extends AbstractManifestTest
{
    const SCHEMA_URL = 'farah://slothsoft@farah/schema/module/latest/module';
    protected function getManifestPath() {
        return $this->getManifest()->getPath();
    }
    
    public function testSchemaExists() {
        $path = self::SCHEMA_URL;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }
    /**
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path) {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }
    
    /**
     * 
     */
    public function testManifestExists() {
        $path = $this->getManifestPath();
        $this->assertFileExists($path, 'Asset file not found!');
        return $path;
    }
    /**
     * @depends testManifestExists
     */
    public function testManifestIsValidXml(string $path) {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertTrue($document instanceof DOMDocument);
        return $document;
    }
    
    
    /**
     * @depends testManifestIsValidXml
     * @depends testSchemaIsValidXml
     */
    public function testManifestIsValidAccordingToSchema($manifestDocument, $schemaDocument) {
        $validateResult = $manifestDocument->schemaValidate($schemaDocument->documentURI);
        $this->assertTrue($validateResult, 'Asset file is invalid!');
        return $manifestDocument;
    }
}


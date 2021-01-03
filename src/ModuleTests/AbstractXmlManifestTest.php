<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use DOMDocument;
use Throwable;

abstract class AbstractXmlManifestTest extends AbstractManifestTest {

    abstract protected static function getManifestDirectory(): string;

    protected static function getManifestFile(): string {
        return static::getManifestDirectory() . DIRECTORY_SEPARATOR . 'manifest.xml';
    }

    protected static function loadTree(): LeanElement {
        return LeanElement::createTreeFromDOMDocument(DOMHelper::loadDocument(static::getManifestFile()));
    }

    const SCHEMA_URL = 'farah://slothsoft@farah/schema/module/';

    /**
     *
     * @depends testManifestIsValidXml
     */
    public function testSchemaExists($manifestDocument): string {
        $version = $manifestDocument->documentElement->hasAttribute('version') ? $manifestDocument->documentElement->getAttribute('version') : '1.0';
        $path = static::SCHEMA_URL . $version;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }

    /**
     *
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path): DOMDocument {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     */
    public function testManifestExists(): string {
        $path = $this->getManifestFile();
        $this->assertFileExists($path, 'Asset file not found!');
        return $path;
    }

    /**
     *
     * @depends testManifestExists
     */
    public function testManifestIsValidXml(string $path): DOMDocument {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     *
     * @depends testManifestIsValidXml
     * @depends testSchemaIsValidXml
     */
    public function testManifestIsValidAccordingToSchema($manifestDocument, $schemaDocument): DOMDocument {
        try {
            $validateResult = $manifestDocument->schemaValidate($schemaDocument->documentURI);
        } catch (Throwable $e) {
            $validateResult = false;
            $this->failException($e);
        }
        $this->assertTrue($validateResult, 'Asset file is invalid!');
        return $manifestDocument;
    }
}


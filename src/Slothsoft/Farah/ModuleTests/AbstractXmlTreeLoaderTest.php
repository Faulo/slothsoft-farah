<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\DOMHelper;
use DOMDocument;
use Throwable;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\XmlTreeLoader;

abstract class AbstractXmlTreeLoaderTest extends AbstractTreeLoaderTest
{

    protected static function getTreeLoader(): TreeLoaderStrategyInterface
    {
        return new XmlTreeLoader();
    }

    const SCHEMA_URL = 'farah://slothsoft@farah/schema/module/1.0';

    protected function getManifestPath(): string
    {
        return static::getManifestDirectory() . DIRECTORY_SEPARATOR . 'manifest.xml';
    }

    public function testSchemaExists(): string
    {
        $path = static::SCHEMA_URL;
        $this->assertFileExists($path, 'Schema file not found!');
        return $path;
    }

    /**
     *
     * @depends testSchemaExists
     */
    public function testSchemaIsValidXml(string $path): DOMDocument
    {
        $dom = new DOMHelper();
        $document = $dom->load($path);
        $this->assertInstanceOf(DOMDocument::class, $document);
        return $document;
    }

    /**
     */
    public function testManifestExists(): string
    {
        $path = $this->getManifestPath();
        $this->assertFileExists($path, 'Asset file not found!');
        return $path;
    }

    /**
     *
     * @depends testManifestExists
     */
    public function testManifestIsValidXml(string $path): DOMDocument
    {
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
    public function testManifestIsValidAccordingToSchema($manifestDocument, $schemaDocument): DOMDocument
    {
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


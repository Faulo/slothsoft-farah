<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsTrue;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;
use DOMDocument;
use DOMNodeList;

/**
 * FromManifestExecutableBuilderTest
 *
 * @see FromManifestExecutableBuilder
 */
class FromManifestExecutableBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FromManifestExecutableBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\FromManifestExecutableBuilder'!");
    }
    
    /**
     *
     * @dataProvider provideURLsAndQuery
     */
    public function testManifestIsValidXml(string $url): void {
        FileSystem::removeDir(ServerEnvironment::getCacheDirectory(), true);
        
        $xml = file_get_contents($url);
        
        $doc = new DOMDocument();
        
        $this->assertThat($doc->loadXML($xml), new IsTrue());
    }
    
    /**
     *
     * @dataProvider provideURLsAndQuery
     */
    public function testManifestContainsDocument(string $url, string $query): void {
        FileSystem::removeDir(ServerEnvironment::getCacheDirectory(), true);
        
        $xpath = DOMHelper::loadXPath(DOMHelper::loadDocument($url));
        
        /** @var DOMNodeList $result */
        $result = $xpath->evaluate($query);
        
        $this->assertGreaterThan(0, $result->length);
    }
    
    public static function provideURLsAndQuery(): array {
        return [
            "No parameter should load manifest-info" => [
                'farah://slothsoft@farah/',
                '/sfm:fragment-info/sfm:manifest-info'
            ],
            "Parameter 'children' should load document-info for root" => [
                'farah://slothsoft@farah/?load=children',
                '/sfm:fragment-info/sfm:document-info'
            ],
            "Parameter 'children' should load manifest-info for descendants" => [
                'farah://slothsoft@farah/?load=children',
                '/sfm:fragment-info/sfm:document-info/sfm:fragment-info/sfm:manifest-info'
            ],
            "No parameter should load manifest-info for resource-directory" => [
                'farah://slothsoft@farah/js/',
                '/sfm:fragment-info/sfm:manifest-info'
            ],
            "Parameter 'children' should load document-info for resource-directory" => [
                'farah://slothsoft@farah/js/?load=children',
                '/sfm:fragment-info/sfm:document-info'
            ],
            "Parameter 'tree' should load document-info for descendants" => [
                'farah://slothsoft@farah/?load=tree',
                '/sfm:fragment-info/sfm:document-info/sfm:fragment-info/sfm:document-info'
            ]
        ];
    }
}
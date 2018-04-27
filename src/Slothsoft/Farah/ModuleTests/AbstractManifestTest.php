<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use DOMDocument;

abstract class AbstractManifestTest extends AbstractTestCase
{

    abstract protected static function loadManifest(): ManifestInterface;

    protected function getManifest()
    {
        static $manifest;
        if ($manifest === null) {
            $manifest = static::loadManifest();
        }
        return $manifest;
    }

    protected function getManifestRoot(): LeanElement
    {
        return $this->getManifest()->getRootElement();
    }

    protected function getManifestDocument(): DOMDocument
    {
        return $this->getManifestRoot()->toDocument();
    }

    public function testHasRootElement()
    {
        $this->assertInstanceOf(LeanElement::class, $this->getManifestRoot());
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testRootElementIsAssets()
    {
        $this->assertEquals($this->getManifestRoot()
            ->getTag(), Module::TAG_ASSET_ROOT);
    }

    /**
     *
     * @dataProvider customAssetClassProvider
     */
    public function testCustomAssetsImplementAssetInterface($assetClassName)
    {
        $this->assertNotNull($assetClassName);
        $this->assertTrue(class_exists($assetClassName));
        $this->assertInstanceOf(AssetInterface::class, new $assetClassName());
    }

    public function customAssetClassProvider()
    {
        $ret = [];
        
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('custom-asset');
        foreach ($nodeList as $node) {
            $className = $node->getAttribute('class');
            $ret[$className] = [
                $className
            ];
        }
        
        return $ret;
    }
}


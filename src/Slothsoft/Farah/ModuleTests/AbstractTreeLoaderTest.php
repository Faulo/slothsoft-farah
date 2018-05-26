<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\InstructionStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\PathResolverStrategyInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;
use DOMDocument;

abstract class AbstractTreeLoaderTest extends AbstractTestCase
{
    
    abstract protected static function getTreeLoader() : TreeLoaderStrategyInterface;
    
    abstract protected static function getManifestDirectory(): string;

    protected function getManifestRoot(): LeanElement
    {
        return static::getTreeLoader()->loadTree(null, static::getManifestDirectory());
    }

    protected function getManifestDocument(): DOMDocument
    {
        return $this->getManifestRoot()->toDocument();
    }
    
    
    

    public function testHasRootElement() : void
    {
        $this->assertInstanceOf(LeanElement::class, $this->getManifestRoot());
    }

    /**
     *
     * @depends testHasRootElement
     */
    public function testRootElementIsAssets()
    {
        $this->assertEquals($this->getManifestRoot()->getTag(), Manifest::TAG_ASSET_ROOT);
    }

    /**
     *
     * @dataProvider customPathResolverProvider
     */
    public function testClassImplementsPathResolverStrategy(string $className) : void
    {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(PathResolverStrategyInterface::class, new $className());
    }
    
    public function customPathResolverProvider() : iterable
    {
        foreach ($this->getAllAttributeValues('path-resolver') as $className) {
            yield $className => [$className];
        }
    }
    
    /**
     *
     * @dataProvider customExecutableBuilderProvider
     */
    public function testClassImplementsExecutableBuilderStrategy(string $className) : void
    {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(ExecutableBuilderStrategyInterface::class, new $className());
    }
    
    public function customExecutableBuilderProvider() : iterable
    {
        foreach ($this->getAllAttributeValues('executable-builder') as $className) {
            yield $className => [$className];
        }
    }
    
    /**
     *
     * @dataProvider customInstructionProvider
     */
    public function testClassImplementsInstructionStrategy(string $className) : void
    {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(InstructionStrategyInterface::class, new $className());
    }
    
    public function customInstructionProvider() : iterable
    {
        foreach ($this->getAllAttributeValues('instruction') as $className) {
            yield $className => [$className];
        }
    }
    
    /**
     *
     * @dataProvider customParameterFilterProvider
     */
    public function testClassImplementsParameterFilterStrategy(string $className) : void
    {
        $this->assertNotNull($className);
        $this->assertTrue(class_exists($className));
        $this->assertInstanceOf(ParameterFilterStrategyInterface::class, new $className());
    }
    
    public function customParameterFilterProvider() : iterable
    {
        foreach ($this->getAllAttributeValues('parameter-filter') as $className) {
            yield $className => [$className];
        }
    }
    
    private function getAllAttributeValues(string $attributeName) : iterable {
        $attributes = [];
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            if ($node->hasAttribute($attributeName)) {
                $attributeValue = $node->getAttribute($attributeName);
                if (!isset($attributes[$attributeValue])) {
                    $attributes[$attributeValue] = true;
                    yield $attributeValue;
                }
            }
        }
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;
use DOMElement;
use Throwable;
use Traversable;

abstract class AbstractModuleTest extends AbstractTestCase
{

    abstract protected static function loadModule(): Module;

    protected function getModule(): Module
    {
        static $module;
        if ($module === null) {
            $module = static::loadModule();
        }
        return $module;
    }
    protected function getModuleProperty(string $name) {
        $module = $this->getModule();
        $getProperty = function(string $name) {
            return $this->$name;
        };
        $getProperty = $getProperty->bindTo($module, get_class($module));
        return $getProperty($name);
    }
    
    protected function getModuleId(): FarahUrl
    {
        return $this->getModule()->getId();
    }
    protected function getModuleUrl(): FarahUrl
    {
        return $this->getModule()->createUrl();
    }
    protected function getModuleAuthority(): AssetInterface
    {
        return $this->getModuleProperty('authority');
    }
    protected function getModuleRootAsset(): AssetInterface
    {
        return $this->getModule()->lookupAsset('/');
    }
    protected function getAssetDirectory(): string
    {
        return $this->getModuleProperty('assetDirectory');
    }
    protected function getModuleAssetManifest(): ManifestInterface
    {
        return $this->getModuleProperty('assetManifest');
    }
    protected function getModuleAssets(): Traversable
    {
        return $this->getModuleProperty('assets');
    }
    
    
    protected function getManifestRoot(): LeanElement
    {
        return $this->getModuleAssetManifest()->getRootElement();
    }

    protected function getManifestDocument(): DOMDocument
    {
        return $this->getManifestRoot()->toDocument();
    }

    protected function getAssetReferences(): array
    {
        $ret = [];
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            if ($node->hasAttribute('ref')) {
                $ret[] = $node->getAttribute('ref');
            }
        }
        return $ret;
    }

    protected function getAssetPaths(): array
    {
        return $this->buildPathIndex($this->getManifestRoot());
    }

    private function buildPathIndex(LeanElement $element, string $parentPath = '/'): array
    {
        $ret = [];
        $name = $element->getAttribute(Module::ATTR_NAME, '');
        $path = $parentPath === '/' ? "/$name" : "$parentPath/$name";
        $ret[] = $path;
        foreach ($element->getChildren() as $childElement) {
            if (in_array($childElement->getTag(), Module::TAGS_ASSETS)) {
                $ret = array_merge($ret, $this->buildPathIndex($childElement, $path));
            }
        }
        return $ret;
    }

    protected function lookupAllModuleNodes(): array
    {
        return $this->buildNodeIndex($this->getModuleRootAsset());
    }

    private function buildNodeIndex(ModuleNodeInterface $node): array
    {
        $ret = [];
        $ret[] = $node;
        foreach ($node->getChildren() as $child) {
            $ret = array_merge($ret, $this->buildNodeIndex($child));
        }
        return $ret;
    }

    protected function lookupAllAssets(): array
    {
        return array_filter($this->lookupAllModuleNodes(), function (ModuleNodeInterface $node) {
            return $node instanceof AssetInterface;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function testAssetDirectoryExists()
    {
        $path = $this->getAssetDirectory();
        $this->assertFileExists($path, 'Asset directory not found!');
    }

    /**
     *
     * @dataProvider assetReferenceProvider
     */
    public function testAssetReferenceIsValid($ref)
    {
        try {
            $url = FarahUrl::createFromReference($ref, $this->getModuleUrl());
            $this->assertInstanceOf(FarahUrl::class, $url);
        } catch (MalformedUrlException $e) {
            $this->failException($e);
        } catch (ProtocolNotSupportedException $e) {
            $this->failException($e);
        }
    }

    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetModuleExists($url)
    {
        try {
            $module = FarahUrlResolver::resolveToModule($url);
            $this->assertInstanceOf(Module::class, $module);
        } catch (ModuleNotFoundException $e) {
            $this->failException($e);
        }
    }

    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetPathExists($url)
    {
        try {
            $asset = FarahUrlResolver::resolveToAsset($url);
            $this->assertInstanceOf(AssetInterface::class, $asset);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }

    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetResultExists($url)
    {
        try {
            $result = FarahUrlResolver::resolveToResult($url);
            $this->assertInstanceOf(ResultInterface::class, $result);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }

    public function assetReferenceProvider()
    {
        $ret = [];
        foreach ($this->getAssetReferences() as $ref) {
            $ret[$ref] = [
                $ref
            ];
        }
        return $ret;
    }

    public function assetReferenceUrlProvider()
    {
        $ret = [];
        foreach ($this->getAssetReferences() as $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $this->getModuleUrl());
                $key = sprintf('%3d: %s', count($ret), (string) $url);
                $ret[$key] = [
                    $url
                ];
            } catch (Throwable $e) {}
        }
        return $ret;
    }

    /**
     *
     * @dataProvider assetPathUrlProvider
     */
    public function testLocalAssetPathExists($url)
    {
        try {
            $asset = FarahUrlResolver::resolveToAsset($url);
            $this->assertInstanceOf(AssetInterface::class, $asset);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }

    public function assetPathUrlProvider()
    {
        $ret = [];
        foreach ($this->getAssetPaths() as $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $this->getModuleUrl());
                $key = sprintf('%3d: %s', count($ret), (string) $url);
                $ret[$key] = [
                    $url
                ];
            } catch (Throwable $e) {}
        }
        return $ret;
    }

    /**
     *
     * @dataProvider assetProvider
     */
    public function testLocalAssetResultExists(AssetInterface $asset)
    {
        try {
            $result = $asset->lookupExecutable();
            $this->assertInstanceOf(ExecutableInterface::class, $result);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }

    /**
     *
     * @depends      testLocalAssetResultExists
     * @dataProvider assetProvider
     */
    public function testLocalAssetResultIsValidAccordingToSchema(AssetInterface $asset)
    {
        $executable = $asset->lookupExecutable();
        $result = $executable->lookupXmlResult();
        
        $document = $result->toDocument();
        $node = $document->documentElement;
        
        $this->assertInstanceOf(DOMElement::class, $node);
        $ns = $node->namespaceURI;
        $version = $node->getAttribute('version');
        if ($ns !== null and $version !== '') {
            if (strpos($ns, 'http://schema.slothsoft.net/') === 0) {
                $schema = explode('/', substr($ns, strlen('http://schema.slothsoft.net/')));
                $this->assertEquals(2, count($schema), "Invalid slothsoft schema: $ns");
                
                $url = "farah://slothsoft@$schema[0]/schema/$schema[1]/$version";
                
                try {
                    $validateResult = $document->schemaValidate($url);
                } catch (Throwable $e) {
                    $validateResult = false;
                    $this->failException($e);
                }
                $this->assertTrue($validateResult, 'Slothsoft document did not pass its own vaidation!');
            }
        }
    }

    /**
     *
     * @dataProvider assetProvider
     */
    public function testLocalPhysicalAssetExists(AssetInterface $asset)
    {
        if ($asset instanceof PhysicalAssetInterface) {
            if ($asset->isDirectory()) {
                $this->assertDirectoryExists($asset->getRealPath());
            }
            if ($asset->isFile()) {
                $this->assertFileExists($asset->getRealPath());
            }
        } else {
            $this->assertTrue(true);
        }
    }

    public function assetProvider()
    {
        $ret = [];
        foreach ($this->lookupAllAssets() as $node) {
            $key = sprintf('%3d: %s', count($ret), (string) $node);
            $ret[$key] = [
                $node
            ];
        }
        return $ret;
    }

    /**
     *
     * @dataProvider nodeProvider
     */
    public function testUseInstructionImplementsInstructionInterface(ModuleNodeInterface $node)
    {
        $this->assertTrue(true); // let's not generate a warning if $node isn't any sort of isUse*
        if ($node->isUseDocument()) {
            $this->assertInstanceOf(UseDocumentInstructionInterface::class, $node);
        }
        if ($node->isUseManifest()) {
            $this->assertInstanceOf(UseManifestInstructionInterface::class, $node);
        }
        if ($node->isUseTemplate()) {
            $this->assertInstanceOf(UseTemplateInstructionInterface::class, $node);
        }
        if ($node->isUseScript()) {
            $this->assertInstanceOf(UseScriptInstructionInterface::class, $node);
        }
        if ($node->isUseStylesheet()) {
            $this->assertInstanceOf(UseStylesheetInstructionInterface::class, $node);
        }
    }

    public function nodeProvider()
    {
        $ret = [];
        foreach ($this->lookupAllModuleNodes() as $node) {
            $key = sprintf('%3d: %s', count($ret), (string) $node);
            $ret[$key] = [
                $node
            ];
        }
        return $ret;
    }
}


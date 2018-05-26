<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use DOMDocument;
use DOMElement;
use Throwable;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;



abstract class AbstractManifestTest extends AbstractTestCase
{
    abstract protected static function getManifestAuthority(): FarahUrlAuthority;

    protected function getManifest() : ManifestInterface {
        static $manifest;
        if ($manifest === null) {
            $manifest = Module::resolveToManifest($this->getManifestUrl());
        }
        return $manifest;
    }
    
    protected function getManifestUrl() : FarahUrl {
        return FarahUrl::createFromComponents(static::getManifestAuthority());
    }
    
    protected function getManifestAsset() : AssetInterface {
        return $this->getManifest()->lookupAsset('/');
    }
    
    protected function getManifestDocument(): DOMDocument
    {
        return $this->getManifestMethod('getRootElement')->toDocument();
    }
    
    protected function getManifestProperty(string $name) {
        return $this->getObjectProperty($this->getManifest(), $name);
    }
    
    protected function getManifestMethod(string $name) {
        return $this->getObjectMethod($this->getManifest(), $name);
    }
    
    
    /**
     *
     * @dataProvider assetReferenceProvider
     */
    public function testAssetReferenceIsValid(string $ref, FarahUrl $context) : void
    {
        try {
            FarahUrl::createFromReference($ref, $context);
            $this->assertTrue(true);
        } catch (MalformedUrlException $e) {
            $this->failException($e);
        } catch (ProtocolNotSupportedException $e) {
            $this->failException($e);
        }
    }
    public function assetReferenceProvider() : iterable
    {
        foreach ($this->getReferencedAssetReferences() as $context => $path) {
            yield (string) $path => [$path, $context];
        }
    }
    public function assetReferenceUrlProvider() : iterable
    {
        foreach ($this->getReferencedAssetReferences() as $context => $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $context);
                yield (string) $url => [$url];
            } catch (Throwable $e) {}
        }
    }
    private function getReferencedAssetReferences(): iterable
    {
        $manifestDocument = $this->getManifestDocument();
        $nodeList = $manifestDocument->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            if ($node->hasAttribute('ref')) {
                yield $this->getContextUrlForManifestNode($node) => $node->getAttribute('ref');
            }
        }
    }
    private function getContextUrlForManifestNode(DOMElement $node) : FarahUrl {
        $path = [];
        while ($node->parentNode instanceof DOMElement) {
            if ($node->hasAttribute('name')) {
                $path[] = $node->getAttribute('name');
            } else {
                $path[] = '*';
            }
            $node = $node->parentNode;
        }
        $path = array_reverse($path);
        $path = implode('/', $path);
        $path = FarahUrlPath::createFromString($path);
        return $this->getManifestUrl()->withAssetPath($path);
    }
    
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedModuleExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToManifest($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToAsset($url);
            $this->assertTrue(true);
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
    public function testReferencedExecutableExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToExecutable($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    /**
     *
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedResultExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToResult($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    
    
    
    public function assetLocalUrlProvider() : iterable
    {
        foreach ($this->getLocalAssetPaths() as $path) {
            try {
                $url = $this->getManifest()->createUrl($path);
                yield (string) $url => [$url];
            } catch (Throwable $e) {}
        }
    }
    private function getLocalAssetPaths(): iterable
    {
        return $this->buildPathIndex($this->getManifestAsset());
    }
    private function buildPathIndex(AssetInterface $asset): iterable
    {
        yield $asset->getUrlPath();
        foreach ($asset->getAssetChildren() as $childAsset) {
            foreach ($this->buildPathIndex($childAsset) as $url) {
                yield $url;
            }
        }
    }
    
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalAssetExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToAsset($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalExecutableExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToExecutable($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    /**
     *
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalResultExists(FarahUrl $url) : void
    {
        try {
            Module::resolveToResult($url);
            $this->assertTrue(true);
        } catch (ModuleNotFoundException $e) {
            $this->assertTrue(true);
        } catch (AssetPathNotFoundException $e) {
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->failException($e);
        }
    }
    /**
     *
     * @depends      testLocalResultExists
     * @dataProvider assetLocalUrlProvider
     */
    public function testLocalResultIsValidAccordingToSchema(FarahUrl $url) : void
    {
        $asset = Module::resolveToAsset($url);
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
}


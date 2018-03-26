<?php
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use DOMDocument;
use Throwable;

abstract class AbstractModuleTest extends TestCase
{
    abstract protected static function loadModule() : Module;
    
    protected function getModule() : Module {
        static $module;
        if ($module === null) {
            $module = static::loadModule();
        }
        return $module;
    }
    protected function getModuleAuthority() : FarahUrlAuthority {
        return $this->getModule()->getAuthority();
    }
    protected function getAssetDirectory() : string {
        return $this->getModule()->getAssetDirectory();
    }
    protected function getManifestRoot() : LeanElement {
        return $this->getModule()->getManifest()->getRootElement();
    }
    protected function getManifestDocument() : DOMDocument {
        return $this->getManifestRoot()->toDocument();
    }
    protected function getAssetReferences() : array {
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
    protected function getAssetPaths() : array {
        return $this->buildIndex($this->getManifestRoot());
    }
    private function buildIndex(LeanElement $element, string $parentPath = '/') : array {
        $ret = [];
        $name = $element->getAttribute(Module::ATTR_NAME, '');
        $path = $parentPath === '/'
            ? "/$name"
            : "$parentPath/$name";
            $ret[] = $path;
            foreach ($element->getChildren() as $childElement) {
                if (in_array($childElement->getTag(), Module::TAGS_ASSETS)) {
                    $ret = array_merge($ret, $this->buildIndex($childElement, $path));
                }
            }
            return $ret;
    }
    
    private function failException(Throwable $e) {
        $this->fail(sprintf('%s: %s', basename(get_class($e)), $e->getMessage()));
    }
    
    public function testAssetDirectoryExists() {
        $path = $this->getAssetDirectory();
        $this->assertFileExists($path, 'Asset directory not found!');
    }
    
    /**
     * @dataProvider assetReferenceProvider
     */
    public function testAssetReferenceIsValid($ref) {
        try {
            $url = FarahUrl::createFromReference($ref, $this->getModuleAuthority());
        } catch(MalformedUrlException $e) {
            $this->failException($e);
        } catch(ProtocolNotSupportedException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetModuleExists($url) {
        try {
            FarahUrlResolver::resolveToModule($url);
        } catch(ModuleNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetPathExists($url) {
        try {
            FarahUrlResolver::resolveToAsset($url);
        } catch(ModuleNotFoundException $e) {
        } catch(AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetReferenceUrlProvider
     */
    public function testReferencedAssetResultExists($url) {
        try {
            FarahUrlResolver::resolveToResult($url);
        } catch(ModuleNotFoundException $e) {
        } catch(AssetPathNotFoundException $e) {
        } catch(Throwable $e) {
            $this->failException($e);
        }
    }
    
    public function assetReferenceProvider()
    {
        $ret = [];
        foreach ($this->getAssetReferences() as $ref) {
            $ret[$ref] = [$ref];
        }
        return $ret;
    }
    public function assetReferenceUrlProvider()
    {
        $ret = [];
        foreach ($this->getAssetReferences() as $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $this->getModuleAuthority());
                $ret[(string) $url] = [$url];
            } catch(Throwable $e) {
            }
        }
        return $ret;
    }
    
    
    /**
     * @dataProvider assetPathUrlProvider
     */
    public function testLocalAssetPathExists($url) {
        try {
            FarahUrlResolver::resolveToAsset($url);
        } catch(ModuleNotFoundException $e) {
        } catch(AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetPathUrlProvider
     */
    public function testLocalAssetResultExists($url) {
        try {
            FarahUrlResolver::resolveToResult($url);
        } catch(ModuleNotFoundException $e) {
        } catch(AssetPathNotFoundException $e) {
        } catch(Throwable $e) {
            $this->failException($e);
        }
    }
    public function assetPathUrlProvider()
    {
        $ret = [];
        foreach ($this->getAssetPaths() as $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $this->getModuleAuthority());
                $ret[(string) $url] = [$url];
            } catch(Throwable $e) {
            }
        }
        return $ret;
    }
}


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
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;
use DOMDocument;
use Throwable;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

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
    protected function getModuleRoot() : AssetInterface {
        return $this->getModule()->getRootAsset();
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
        return $this->buildPathIndex($this->getManifestRoot());
    }
    private function buildPathIndex(LeanElement $element, string $parentPath = '/') : array {
        $ret = [];
        $name = $element->getAttribute(Module::ATTR_NAME, '');
        $path = $parentPath === '/'
            ? "/$name"
            : "$parentPath/$name";
            $ret[] = $path;
            foreach ($element->getChildren() as $childElement) {
                if (in_array($childElement->getTag(), Module::TAGS_ASSETS)) {
                    $ret = array_merge($ret, $this->buildPathIndex($childElement, $path));
                }
            }
            return $ret;
    }
    protected function getModuleNodes() : array {
        return $this->buildNodeIndex($this->getModuleRoot());
    }
    private function buildNodeIndex(ModuleNodeInterface $node) : array {
        $ret = [];
        $ret[] = $node;
        foreach ($node->getChildren() as $child) {
            $ret = array_merge($ret, $this->buildNodeIndex($child));
        }
        return $ret;
    }
    protected function getModuleAssets() : array {
        return array_filter(
            $this->getModuleNodes(),
            function(ModuleNodeInterface $node) {
                return $node instanceof AssetInterface;
            },
            ARRAY_FILTER_USE_BOTH
        );
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
                $key = sprintf('%3d: %s', count($ret), (string) $url);
                $ret[$key] = [$url];
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
    public function assetPathUrlProvider()
    {
        $ret = [];
        foreach ($this->getAssetPaths() as $ref) {
            try {
                $url = FarahUrl::createFromReference($ref, $this->getModuleAuthority());
                $key = sprintf('%3d: %s', count($ret), (string) $url);
                $ret[$key] = [$url];
            } catch(Throwable $e) {
            }
        }
        return $ret;
    }
    /**
     * @dataProvider assetProvider
     */
    public function testLocalAssetResultExists(AssetInterface $asset) {
        try {
            $asset->lookupResultByArguments(FarahUrlArguments::createEmpty());
        } catch(Throwable $e) {
            $this->failException($e);
        }
    }
    public function assetProvider()
    {
        $ret = [];
        foreach ($this->getModuleAssets() as $node) {
            $key = sprintf('%3d: %s', count($ret), (string) $node);
            $ret[$key] = [$node];
        }
        return $ret;
    }
    /**
     * @dataProvider nodeProvider
     */
    public function testUseInstructionImplementsInstructionInterface(ModuleNodeInterface $node) {
        $this->assertTrue(true); //let's not generate a warning if $node isn't any sort of isUse*
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
        foreach ($this->getModuleNodes() as $node) {
            $key = sprintf('%3d: %s', count($ret), (string) $node);
            $ret[$key] = [$node];
        }
        return $ret;
    }
}


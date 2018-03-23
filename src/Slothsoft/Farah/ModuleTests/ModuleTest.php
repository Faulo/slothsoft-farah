<?php
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use DOMDocument;
use Throwable;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;

abstract class ModuleTest extends TestCase
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
    protected function getManifestDocument() : DOMDocument {
        return $this->getModule()->getManifest()->getRootElement()->toDocument();
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
    public function testAssetReferenceModuleExists($url) {
        try {
            FarahUrlResolver::resolveToModule($url);
        } catch(ModuleNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetReferenceUrlProvider
     */
    public function testAssetReferencePathExists($url) {
        try {
            FarahUrlResolver::resolveToAsset($url);
        } catch(AssetPathNotFoundException $e) {
            $this->failException($e);
        }
    }
    /**
     * @dataProvider assetReferenceUrlProvider
     */
    public function testAssetReferenceResultExists($url) {
        try {
            FarahUrlResolver::resolveToResult($url);
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
}


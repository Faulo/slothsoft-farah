<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Ds\Map;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\FileNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Throwable;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;

class FromSubManifestPathResolver implements PathResolverStrategyInterface {
    
    private Map $assets;
    
    public function __construct() {
        $this->assets = new Map();
    }
    
    private function loadSubManifest(AssetInterface $context): array {
        if ($this->assets->hasKey($context)) {
            return $this->assets->get($context);
        }
        
        $manifest = $context->getManifest();
        $element = $context->getManifestElement();
        $directory = $element->getAttribute(Manifest::ATTR_PATH);
        
        $xmlFile = $manifest->createManifestFile($directory . DIRECTORY_SEPARATOR . 'manifest.xml');
        
        if (! $xmlFile->isFile()) {
            throw new FileNotFoundException($xmlFile);
        }
        
        $tmpFile = $manifest->createCacheFile('manifest.tmp', $directory, FarahUrlArguments::createFromValueList([
            'path' => $xmlFile->getRealPath()
        ]));
        
        if ($tmpFile->isFile()) {
            if ($tmpFile->getMTime() > $xmlFile->getMTime()) {
                $children = [];
                try {
                    $subManifest = unserialize(file_get_contents((string) $tmpFile), [
                        'allowed_classes' => [
                            LeanElement::class
                        ]
                    ]);
                    
                    foreach ($subManifest->getChildren() as $child) {
                        $name = $child->getAttribute(Manifest::ATTR_NAME);
                        $children[$name] = $child;
                    }
                } catch (Throwable $e) {}
                $this->assets->put($context, $children);
                return $children;
            }
        }
        
        $dom = new DOMHelper();
        $subManifest = LeanElement::createTreeFromDOMDocument($dom->loadDocument($xmlFile->getRealPath()));
        
        foreach ([
            Manifest::ATTR_ID,
            Manifest::ATTR_NAME,
            Manifest::ATTR_ASSETPATH,
            Manifest::ATTR_PATH,
            Manifest::ATTR_REALPATH
        ] as $attr) {
            $subManifest->setAttribute($attr, $element->getAttribute($attr));
        }
        
        $children = [];
        foreach ($subManifest->getChildren() as $child) {
            $manifest->normalizeManifestElement($child, $subManifest);
            $name = $child->getAttribute(Manifest::ATTR_NAME);
            $children[$name] = $child;
        }
        
        file_put_contents((string) $tmpFile, serialize($subManifest));
        
        $this->assets->put($context, $children);
        return $children;
    }
    
    public function loadChildren(AssetInterface $context): iterable {
        $children = $this->loadSubManifest($context);
        
        return array_keys($children);
    }
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        $children = $this->loadSubManifest($context);
        
        if (! isset($children[$name])) {
            throw new AssetPathNotFoundException($context, $name, array_keys($children));
        }
        
        return $children[$name];
    }
}


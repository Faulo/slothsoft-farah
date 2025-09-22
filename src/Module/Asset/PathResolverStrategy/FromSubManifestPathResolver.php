<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\FileNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Throwable;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;

class FromSubManifestPathResolver implements PathResolverStrategyInterface {
    
    private ?LeanElement $subManifest = null;
    
    private array $children = [];
    
    private function loadSubManifest(AssetInterface $context): void {
        if ($this->subManifest) {
            return;
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
                try {
                    $this->subManifest = unserialize(file_get_contents((string) $tmpFile), [
                        'allowed_classes' => [
                            LeanElement::class
                        ]
                    ]);
                    
                    foreach ($this->subManifest->getChildren() as $child) {
                        $name = $child->getAttribute(Manifest::ATTR_NAME);
                        $this->children[$name] = $child;
                    }
                    
                    return;
                } catch (Throwable $e) {
                    $this->children = [];
                }
            }
        }
        
        $dom = new DOMHelper();
        $this->subManifest = LeanElement::createTreeFromDOMDocument($dom->loadDocument($xmlFile->getRealPath()));
        
        foreach ([
            Manifest::ATTR_ID,
            Manifest::ATTR_NAME,
            Manifest::ATTR_ASSETPATH,
            Manifest::ATTR_PATH,
            Manifest::ATTR_REALPATH
        ] as $attr) {
            $this->subManifest->setAttribute($attr, $element->getAttribute($attr));
        }
        
        foreach ($this->subManifest->getChildren() as $child) {
            $manifest->normalizeManifestElement($child, $this->subManifest);
            $name = $child->getAttribute(Manifest::ATTR_NAME);
            $this->children[$name] = $child;
        }
        
        file_put_contents((string) $tmpFile, serialize($this->subManifest));
    }
    
    public function loadChildren(AssetInterface $context): iterable {
        $this->loadSubManifest($context);
        
        return array_keys($this->children);
    }
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        $this->loadSubManifest($context);
        
        if (! isset($this->children[$name])) {
            throw new AssetPathNotFoundException($context, $name, array_keys($this->children));
        }
        
        return $this->children[$name];
    }
}


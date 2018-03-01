<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Assets\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *
 */
class DynamicPathResolver implements PathResolverInterface
{
    
    private $asset;
    
    private $pathMap = [];
    
    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
        $this->pathMap['/'] = $this->asset;
    }
    
    public function resolvePath(string $path): AssetInterface
    {
        if (! isset($this->pathMap[$path])) {
            $this->pathMap[$path] = $this->createChildResource($path);
        }
        return $this->pathMap[$path];
    }
    
    private function createChildResource(string $path) : AssetInterface
    {
        assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
        
        $childPath = $match[0];
        $childName = $match[1];
        $descendantPath = substr($path, strlen($childPath));
        
        if ($descendantPath === '') {
            $data = [];
            $data[Module::ATTR_NAME] = $childName;
            $data[Module::ATTR_TYPE] = $this->asset->getElementAttribute(Module::ATTR_TYPE);
            
            return $this->asset->addChildElement(LeanElement::createOneFromArray(Module::TAG_RESOURCE, $data));
        } else {
            return $this->asset->traverseTo($childPath)->traverseTo($descendantPath);
        }
    }
    
    public function getPathMap(): array
    {
        $this->loadResourceDirectory();
        ksort($this->pathMap);
        return $this->pathMap;
    }
    
    private function loadResourceDirectory()
    {
        $mime = $this->asset->getElementAttribute(Module::ATTR_TYPE, '*/*');
        $path = $this->asset->getRealPath();
        assert(is_dir($path), "Path is not a directory: $path");
        
        $fileList = FileSystem::scanDir($path);
        foreach ($fileList as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (MimeTypeDictionary::matchesMime($ext, $mime)) {
                $this->resolvePath("/$name");
            }
        }
    }
}


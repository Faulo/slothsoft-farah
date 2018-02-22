<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{

    private $definition;

    private $pathMap = [];

    public function __construct(AssetDefinitionInterface $definition)
    {
        $this->definition = $definition;
        $this->pathMap['/'] = $this->definition;
    }

    public function resolvePath(string $path): AssetDefinitionInterface
    {
        if (! isset($this->pathMap[$path])) {
            $this->pathMap[$path] = $this->createChildResource($path);
        }
        return $this->pathMap[$path];
    }

    private function createChildResource(string $path)
    {
        assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
        
        $childPath = $match[0];
        $childName = $match[1];
        $descendantPath = substr($path, strlen($childPath));
        
        if ($descendantPath === '') {
            $data = [];
            $data[Module::ATTR_NAME] = $childName;
            $data[Module::ATTR_TYPE] = $this->definition->getElementAttribute(Module::ATTR_TYPE);
            
            return $this->definition->createChildDefinition(LeanElement::createOneFromArray(Module::TAG_RESOURCE, $data));
        } else {
            return $this->definition->traverseTo($childPath)->traverseTo($descendantPath);
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
        $mime = $this->definition->getElementAttribute(Module::ATTR_TYPE, '*/*');
        $path = $this->definition->getRealPath();
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


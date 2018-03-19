<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{

    private $asset;

    private $pathMap = [];

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
        $this->pathMap['/'] = $this->asset;
    }

    public function resolvePath(string $path, bool $isProbablyDirectory = false): AssetInterface
    {
        if (! isset($this->pathMap[$path])) {
            $this->pathMap[$path] = $this->createChildResource($path, $isProbablyDirectory);
        }
        return $this->pathMap[$path];
    }

    private function createChildResource(string $path, bool $isProbablyDirectory): AssetInterface
    {
        assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
        
        $childPath = $match[0];
        $childName = $match[1];
        $descendantPath = substr($path, strlen($childPath));
        
        if ($descendantPath === '') {
            if ($isProbablyDirectory) {
                $element = LeanElement::createOneFromArray(
                    Module::TAG_RESOURCE_DIRECTORY,
                    [
                        Module::ATTR_NAME => $childName,
                        Module::ATTR_PATH => $childName,
                        Module::ATTR_TYPE => $this->asset->getElementAttribute(Module::ATTR_TYPE),                        
                    ]
                );
            } else {
                $element = LeanElement::createOneFromArray(
                    Module::TAG_RESOURCE,
                    [
                        Module::ATTR_NAME => $childName,
                        Module::ATTR_TYPE => $this->asset->getElementAttribute(Module::ATTR_TYPE),
                    ]
                    );
            }
            return $this->asset->createChildNode($element);
        } else {
            return $this->resolvePath($childPath, true)->traverseTo($descendantPath);
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


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetInterface;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\DirectoryAsset\DirectoryAssetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{

    private $asset;

    private $pathMap = [];

    public function __construct(DirectoryAssetInterface $asset)
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

    private function createChildResource(string $path): PhysicalAssetInterface
    {
        assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
        
        $childPath = $match[0];
        $childName = $match[1];
        $descendantPath = substr($path, strlen($childPath));
        
        if ($descendantPath === '') {
            if (is_dir($this->asset->getRealPath() . DIRECTORY_SEPARATOR . $path)) {
                $element = LeanElement::createOneFromArray(Module::TAG_RESOURCE_DIRECTORY, [
                    Module::ATTR_NAME => $childName,
                    Module::ATTR_PATH => $childName,
                    Module::ATTR_TYPE => $this->asset->getElementAttribute(Module::ATTR_TYPE)
                ]);
            } else {
                $element = LeanElement::createOneFromArray(Module::TAG_RESOURCE, [
                    Module::ATTR_NAME => $childName,
                    Module::ATTR_TYPE => $this->asset->getElementAttribute(Module::ATTR_TYPE)
                ]);
            }
            return $this->asset->createChildNode($element);
        } else {
            return $this->resolvePath($childPath, true)->traverseTo($descendantPath);
        }
    }

    public function getPathMap(): array
    {
        ksort($this->pathMap);
        return $this->pathMap;
    }
}


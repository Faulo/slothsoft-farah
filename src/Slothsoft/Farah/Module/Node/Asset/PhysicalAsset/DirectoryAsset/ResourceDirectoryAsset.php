<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\DirectoryAsset;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Module\Node\Enhancements\MimeTypeTrait;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryAsset extends DirectoryAssetImplementation
{
    use MimeTypeTrait;
    
    protected function loadChildren(): array
    {
        $ret = [];
        $mime = $this->getMimeType();
        $path = $this->getRealPath();
        $resolver = $this->getPathResolver();
        
        $fileList = FileSystem::scanDir($path);
        foreach ($fileList as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (MimeTypeDictionary::matchesMime($ext, $mime)) {
                $ret[] = $resolver->resolvePath("/$name");
            }
        }
        return $ret;
    }
    protected function loadPathResolver(): PathResolverInterface
    {
        return PathResolverCatalog::createResourceDirectoryPathResolver($this);
    }
}


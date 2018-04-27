<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

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
class ResourceDirectoryAsset extends PhysicalAssetBase
{
    use MimeTypeTrait;

    protected function loadChildren(): array
    {
        $ret = [];
        $desiredMime = $this->getMimeType();
        $desiredExtension = MimeTypeDictionary::guessExtension($desiredMime);
        $path = $this->getRealPath();
        $resolver = $this->getPathResolver();
        
        $fileList = FileSystem::scanDir($path);
        foreach ($fileList as $file) {
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            if ($desiredExtension === '') {
                if (MimeTypeDictionary::matchesMime($fileExtension, $desiredMime)) {
                    $fileName = $file;
                    $ret[] = $resolver->resolvePath("/$fileName");
                }
            } else {
                if ($fileExtension === $desiredExtension) {
                    $fileName = pathinfo($file, PATHINFO_FILENAME);
                    $ret[] = $resolver->resolvePath("/$fileName");
                }
            }
        }
        return $ret;
    }

    protected function loadPathResolver(): PathResolverInterface
    {
        return PathResolverCatalog::createResourceDirectoryPathResolver($this);
    }
}


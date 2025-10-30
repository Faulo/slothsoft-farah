<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ManifestElementBuilder;
use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FromFilesystemPathResolver implements PathResolverStrategyInterface {
    
    public function loadChildren(AssetInterface $context): iterable {
        $element = $context->getManifestElement();
        $desiredMime = $element->getAttribute(Manifest::ATTR_TYPE, '*/*');
        $desiredExtension = MimeTypeDictionary::guessExtension($desiredMime);
        $path = $element->getAttribute(Manifest::ATTR_REALPATH);
        
        $files = FileSystem::scanDir($path, FileSystem::SCANDIR_FILEINFO);
        /** @var $file \SplFileInfo */
        foreach ($files as $file) {
            if ($file->isDir()) {
                yield $file->getFilename();
            } else {
                $fileExtension = $file->getExtension();
                if ($desiredExtension === '') {
                    if (MimeTypeDictionary::matchesMime($fileExtension, $desiredMime)) {
                        yield $file->getFilename();
                    }
                } else {
                    if ($fileExtension === $desiredExtension) {
                        yield $file->getBasename('.' . $fileExtension);
                    }
                }
            }
        }
    }
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        $element = $context->getManifestElement();
        $type = $element->getAttribute(Manifest::ATTR_TYPE, '*/*');
        $extension = MimeTypeDictionary::guessExtension($type);
        $directory = $element->getAttribute(Manifest::ATTR_REALPATH);
        
        $path = $name;
        
        if (is_dir($directory . DIRECTORY_SEPARATOR . $path)) {
            $child = ManifestElementBuilder::createResourceDirectory($name, $path, $type);
        } else {
            if ($extension !== '') {
                $path .= '.' . $extension;
            }
            if (is_file($directory . DIRECTORY_SEPARATOR . $path)) {
                $child = ManifestElementBuilder::createResource($name, $path, $type);
            } else {
                throw new AssetPathNotFoundException($context, $name);
            }
        }
        
        $context->normalizeManifestElement($child);
        
        return $child;
    }
}


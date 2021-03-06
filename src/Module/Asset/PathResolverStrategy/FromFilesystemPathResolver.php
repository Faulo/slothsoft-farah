<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ManifestElementBuilder;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FromFilesystemPathResolver implements PathResolverStrategyInterface {

    public function loadChildren(AssetInterface $context): iterable {
        $element = $context->getManifestElement();
        $desiredMime = $element->getAttribute('type', '*/*');
        $desiredExtension = MimeTypeDictionary::guessExtension($desiredMime);
        $path = $element->getAttribute('realpath');

        $filePathList = FileSystem::scanDir($path, FileSystem::SCANDIR_EXCLUDE_DIRS);
        foreach ($filePathList as $filePath) {
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            if ($desiredExtension === '') {
                if (MimeTypeDictionary::matchesMime($fileExtension, $desiredMime)) {
                    yield pathinfo($filePath, PATHINFO_BASENAME);
                }
            } else {
                if ($fileExtension === $desiredExtension) {
                    yield pathinfo($filePath, PATHINFO_FILENAME);
                }
            }
        }
    }

    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        $element = $context->getManifestElement();
        $type = $element->getAttribute('type', '*/*');
        $extension = MimeTypeDictionary::guessExtension($type);
        $directory = $element->getAttribute('realpath');

        $path = $name;

        if (is_dir($directory . DIRECTORY_SEPARATOR . $path)) {
            $child = ManifestElementBuilder::createResourceDirectory($name, $path, $type);
        } else {
            if ($extension !== '') {
                $path .= ".$extension";
            }
            $child = ManifestElementBuilder::createResource($name, $path, $type);
        }

        $context->normalizeManifestElement($child);

        return $child;
    }
}


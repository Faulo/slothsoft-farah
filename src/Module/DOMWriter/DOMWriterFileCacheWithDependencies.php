<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMDocument;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterFileCache;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use SplFileInfo;

/**
 * File-backed DOM writer cache that refreshes when dependent files change.
 *
 * @author Daniel Schulz
 * @since 2026-01-15
 */
final class DOMWriterFileCacheWithDependencies implements DOMWriterInterface, FileWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterFileCache $cacheWriter;
    
    public function __construct(DOMWriterInterface $writer, SplFileInfo $cacheFile, string ...$dependentFiles) {
        $this->cacheWriter = new DOMWriterFileCache($writer, $cacheFile, function (SplFileInfo $cacheFile) use ($dependentFiles): bool {
            foreach ($dependentFiles as $dependentFile) {
                if (filemtime($dependentFile) > $cacheFile->getMTime()) {
                    return true;
                }
            }
            return false;
        });
    }
    
    public function toDocument(): DOMDocument {
        return $this->cacheWriter->toDocument();
    }
    
    public function toFile(): SplFileInfo {
        return $this->cacheWriter->toFile();
    }
}


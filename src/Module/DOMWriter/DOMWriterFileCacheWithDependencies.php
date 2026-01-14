<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterFileCache;
use SplFileInfo;

final class DOMWriterFileCacheWithDependencies extends DOMWriterFileCache {
    
    public function __construct(DOMWriterInterface $writer, SplFileInfo $cacheFile, string ...$dependentFiles) {
        parent::__construct($writer, $cacheFile, function (SplFileInfo $cacheFile) use ($dependentFiles): bool {
            foreach ($dependentFiles as $dependentFile) {
                if (filemtime($dependentFile) > $cacheFile->getMTime()) {
                    return true;
                }
            }
            return false;
        });
    }
}


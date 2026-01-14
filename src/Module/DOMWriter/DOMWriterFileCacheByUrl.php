<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterFileCache;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use SplFileInfo;
use Slothsoft\Core\FileSystem;

final class DOMWriterFileCacheByUrl extends DOMWriterFileCache {
    
    private const DIRECTORY_MAXLENGTH = 128;
    
    private static function truncateSegment(string $segment): string {
        $segment = FileSystem::filenameSanitize($segment);
        return strlen($segment) > self::DIRECTORY_MAXLENGTH ? md5($segment) : $segment;
    }
    
    private static function cacheFile(FarahUrl $url): SplFileInfo {
        $cacheFile = ServerEnvironment::getCacheDirectory();
        
        $cacheFile .= DIRECTORY_SEPARATOR . self::truncateSegment($url->getAssetAuthority()->getVendor() . '@' . $url->getAssetAuthority()->getModule());
        $segments = $url->getAssetPath()->getSegments();
        if ($segments !== []) {
            $cacheFile .= DIRECTORY_SEPARATOR . self::truncateSegment(implode('-', $segments));
        }
        
        $query = $url->getQuery();
        if ($query !== '') {
            $cacheFile .= DIRECTORY_SEPARATOR . self::truncateSegment($query);
        }
        
        $fragment = $url->getFragment();
        if ($fragment === '') {
            $cacheFile .= DIRECTORY_SEPARATOR . "index.xml";
        } else {
            $cacheFile .= DIRECTORY_SEPARATOR . self::truncateSegment("$fragment.xml");
        }
        
        return FileInfoFactory::createFromPath($cacheFile);
    }
    
    public function __construct(FarahUrl $cacheUrl, DOMWriterInterface $writer, string ...$dependentFiles) {
        parent::__construct($writer, self::cacheFile($cacheUrl), function (SplFileInfo $cacheFile) use ($dependentFiles): bool {
            foreach ($dependentFiles as $dependentFile) {
                if (filemtime($dependentFile) > $cacheFile->getMTime()) {
                    return true;
                }
            }
            return false;
        });
    }
}


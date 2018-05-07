<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

class FilePathResult extends ResultBase
{
    private $path;
    public function __construct(string $path) {
        $this->path = $path;
    }
    public function lookupStream() : StreamInterface
    {
        return new LazyOpenStream($this->path, StreamWrapperInterface::MODE_OPEN_READONLY);
    }

    public function lookupMimeType() : string
    {
        return MimeTypeDictionary::guessMime(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function lookupCharset() : string
    {
        return 'UTF-8';
    }

    public function lookupFileName() : string
    {
        return basename($this->path);
    }
    
    public function lookupChangeTime() : int {
        return filemtime($this->path);
    }
    
    public function lookupHash() : string {
        return md5_file($this->path);
    }
    
    public function lookupIsBufferable() : bool {
        return true;
    }
}


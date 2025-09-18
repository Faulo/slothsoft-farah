<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

class IdentityFactory implements FilteredStreamWriterInterface {
    
    public static function getInstance(): self {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    
    public function toFilteredStream(StreamInterface $stream): StreamInterface {
        return $stream;
    }
}


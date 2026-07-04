<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

/**
 * Stream writer factory for identity HTTP coding without transformation.
 *
 * @author Daniel Schulz
 * @since 2018-05-07
 */
final class IdentityFactory implements FilteredStreamWriterInterface {
    
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


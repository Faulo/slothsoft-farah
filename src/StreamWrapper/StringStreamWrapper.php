<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\StreamWrapper\ResourceStreamWrapper;

/**
 * Stream wrapper for exposing strings as readable resources.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class StringStreamWrapper extends ResourceStreamWrapper {
    
    public function __construct(string $contents) {
        $resource = BlobUrl::createTemporaryObject();
        file_put_contents(BlobUrl::createObjectURL($resource), $contents);
        parent::__construct($resource);
    }
}
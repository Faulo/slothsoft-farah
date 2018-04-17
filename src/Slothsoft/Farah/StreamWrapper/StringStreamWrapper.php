<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\StreamWrapper\ResourceStreamWrapper;

class StringStreamWrapper extends ResourceStreamWrapper
{
    public function __construct(string $contents)
    {
        $resource = BlobUrl::createTemporaryObject();
        file_put_contents(BlobUrl::createObjectURL($resource), $contents);
        parent::__construct($resource);
    }
}
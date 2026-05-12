<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\StreamWrapper;

use DOMDocument;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\StreamWrapper\ResourceStreamWrapper;

class DocumentStreamWrapper extends ResourceStreamWrapper {
    
    public function __construct(DOMDocument $doc) {
        $resource = BlobUrl::createTemporaryObject();
        $doc->save(BlobUrl::createObjectURL($resource));
        parent::__construct($resource);
    }
}
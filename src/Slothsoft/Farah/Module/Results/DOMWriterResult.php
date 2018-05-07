<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterResult extends ResultBase implements ResultInterfacePlusXml
{

    private $writer;
    private $resourceUrl;

    public function __construct(DOMWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    
    public function lookupStream() : StreamInterface
    {
        return new LazyOpenStream($this->toResourceUrl(), StreamWrapperInterface::MODE_OPEN_READONLY);
    }
    
    public function lookupMimeType() : string
    {
        return 'application/xml';
    }
    
    public function lookupCharset() : string
    {
        return 'UTF-8';
    }
    
    public function lookupFileName() : string
    {
        return 'result.xml';
    }
    
    public function lookupChangeTime() : int {
        return 0;
    }
    
    public function lookupHash() : string {
        return md5_file($this->toResourceUrl());
    }
    
    public function lookupIsBufferable() : bool {
        return true;
    }
    
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        return $this->writer->toElement($targetDoc);
    }

    public function toDocument() : DOMDocument
    {
        return $this->writer->toDocument();
    }
    
    private function toResourceUrl() {
        if ($this->resourceUrl === null) {
            $this->resourceUrl = BlobUrl::createTemporaryURL();
            $this->writer->toDocument()->save($this->resourceUrl);
        }
        return $this->resourceUrl;
    }

}


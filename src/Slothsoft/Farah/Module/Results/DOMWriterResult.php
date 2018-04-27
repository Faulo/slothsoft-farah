<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
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

    public function __construct(DOMWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    
    public function lookupStream() : StreamInterface
    {
        $resource = BlobUrl::createTemporaryObject();
        $this->writer->toDocument()->save(BlobUrl::createObjectURL($resource));
        rewind($resource);
        return new Stream($resource);
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
    
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        return $this->writer->toElement($targetDoc);
    }

    public function toDocument() : DOMDocument
    {
        return $this->writer->toDocument();
    }

}


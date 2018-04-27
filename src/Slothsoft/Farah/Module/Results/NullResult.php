<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Blob\BlobUrl;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class NullResult extends ResultBase implements ResultInterfacePlusXml
{
    use DOMWriterDocumentFromElementTrait;
    
    public function lookupStream() : StreamInterface
    {
        return new Stream(BlobUrl::createTemporaryObject());
    }
    
    public function lookupMimeType() : string
    {
        return 'text/plain';
    }
    
    public function lookupCharset() : string
    {
        return '';
    }
    
    public function lookupFileName() : string
    {
        return 'null.txt';
    }
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        return $targetDoc->createElement('null');
    }

}


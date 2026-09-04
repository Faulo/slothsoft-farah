<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use DOMDocument;
use DOMElement;
use Generator;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use SplFileInfo;

final class AllWriterMock implements ChunkWriterInterface, DOMWriterInterface, FileWriterInterface, StreamWriterInterface, StringWriterInterface {
    
    public function toChunks(): Generator {
        yield from [];
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        return new DOMElement('mock');
    }
    
    public function toFile(): SplFileInfo {
        return new SplFileInfo(__FILE__);
    }
    
    public function toString(): string {
        return '';
    }
    
    public function toDocument(): DOMDocument {
        return new DOMDocument();
    }
    
    public function toStream(): StreamInterface {
        return Utils::streamFor();
    }
}

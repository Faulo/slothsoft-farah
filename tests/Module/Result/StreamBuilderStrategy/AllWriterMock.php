<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use DOMDocument;
use DOMElement;
use Generator;
use SplFileInfo;

final class AllWriterMock implements ChunkWriterInterface, DOMWriterInterface, FileWriterInterface, StreamWriterInterface, StringWriterInterface {
    
    public function toChunks(): Generator {}
    
    public function toElement(DOMDocument $targetDoc): DOMElement {}
    
    public function toFile(): SplFileInfo {}
    
    public function toString(): string {}
    
    public function toDocument(): DOMDocument {}
    
    public function toStream(): StreamInterface {}
}
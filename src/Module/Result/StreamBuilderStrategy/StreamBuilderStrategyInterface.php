<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *
 */
interface StreamBuilderStrategyInterface {
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface;
    
    /**
     * Create a stream writer for this result.
     */
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface;
    
    /**
     * Create a file writer for this result.
     */
    public function buildFileWriter(ResultInterface $context): FileWriterInterface;
    
    /**
     * Create a chunk writer for this result.
     */
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface;
    
    /**
     * Create a DOM writer for this result.
     */
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface;
    
    /**
     * Determine all available stats of this resource, as a call to stat() would.
     */
    public function buildStreamFileStatistics(ResultInterface $context): array;
    
    /**
     * Determine the filename of this result.
     */
    public function buildStreamFileName(ResultInterface $context): string;
    
    /**
     * Determine the mime type of this result.
     */
    public function buildStreamMimeType(ResultInterface $context): string;
    
    /**
     * Determine the character set of this result
     * Returns a valid character set, or the empty string if not applicable.
     */
    public function buildStreamCharset(ResultInterface $context): string;
    
    /**
     * Determine a hash for this resource, or the empty string if undeterminable
     */
    public function buildStreamHash(ResultInterface $context): string;
    
    /**
     * Determine whether or not intermediate filters are allowed to buffer this result, or should always flush immediately.
     */
    public function buildStreamIsBufferable(ResultInterface $context): bool;
}


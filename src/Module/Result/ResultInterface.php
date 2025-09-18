<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ResultInterface {
    
    /**
     * Create a FarahUrl for this result
     *
     * @return FarahUrl
     */
    public function createUrl(): FarahUrl;
    
    public function lookupStringWriter(): StringWriterInterface;
    
    public function lookupStreamWriter(): StreamWriterInterface;
    
    public function lookupFileWriter(): FileWriterInterface;
    
    public function lookupDOMWriter(): DOMWriterInterface;
    
    public function lookupChunkWriter(): ChunkWriterInterface;
    
    /**
     * Determine all available statistics of this result, as a call to stat() would.
     *
     * @return array
     */
    public function lookupFileStatistics(): array;
    
    /**
     * Look up the time of the last change to this result, or 0 if undeterminable.
     *
     * @return int
     */
    public function lookupFileChangeTime(): int;
    
    /**
     * Look up the size of this result, or 0 if undeterminable.
     *
     * @return int
     */
    public function lookupFileSize(): int;
    
    /**
     * Determine the filename of this result.
     *
     * @return string
     */
    public function lookupFileName(): string;
    
    /**
     * Determine the mime type of this result.
     *
     * @return string
     */
    public function lookupMimeType(): string;
    
    /**
     * Determine the character set of this result
     *
     * @return string A valid character, or the empty string if not applicable.
     */
    public function lookupCharset(): string;
    
    /**
     * Determine a hash for this resource, or the empty string if undeterminable
     *
     * @return string
     */
    public function lookupHash(): string;
    
    /**
     * Determine whether or not intermediate filters are allowed to buffer this result, or should always flush immediately.
     *
     * @return bool
     */
    public function lookupIsBufferable(): bool;
}


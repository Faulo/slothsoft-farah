<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ResultInterface
{
    public function init(ExecutableInterface $ownerExecutable, FarahUrlStreamIdentifier $type);
    
    /**
     * The Farah URL that represents this result.
     *
     * @return string
     */
    public function getId(): string;
    
    /**
     * Create a FarahUrl for this result
     * 
     * @return FarahUrl
     */
    public function createUrl(): FarahUrl;
    
    /**
     * Create a stream of this result.
     * 
     * @return StreamInterface
     */
    public function lookupStream() : StreamInterface;
    
    /**
     * Determine the filename of this result.
     * 
     * @return string
     */
    public function lookupFileName() : string;
    
    /**
     * Determine the mime type of this result.
     * @return string
     */
    public function lookupMimeType() : string;
    
    /**
     * Determine the character set of this result
     * 
     * @return string A valid character, or the empty string if not applicable.
     */
    public function lookupCharset() : string;
    
    /**
     * Determine the time of the last change to this resource, or 0 if undeterminable
     * @return int
     */
    public function lookupChangeTime() : int;
    
    /**
     * Determine a hash for this resource, or the empty string if undeterminable
     * @return string
     */
    public function lookupHash() : string;
}


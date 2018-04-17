<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\StreamWrapper\FileStreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\ResultImplementation;
use Slothsoft\Core\IO\Writable\FileWriterStringFromFileTrait;

abstract class FileResult extends ResultImplementation
{
    use FileWriterStringFromFileTrait;
    
    protected $file;

    public function __construct(FarahUrl $url, HTTPFile $file)
    {
        parent::__construct($url);
        
        $this->file = $file;
    }

    
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface
    {
        return new FileStreamWrapper($this->file);
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return $this->file->exists();
    }
    
   
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toFile()
     */
    public function toFile() : HTTPFile {
        return $this->file;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\StreamWrapper\FileStreamWrapper;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FileWriterResult extends ResultImplementation
{
    private $writer;

    public function __construct(FarahUrl $url, FileWriterInterface $writer)
    {
        parent::__construct($url);
        
        $this->writer = $writer;
    }
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface
    {
        return new FileStreamWrapper($this->writer->toFile());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface
    {
        return new FileStreamWrapper($this->writer->toFile());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return $this->writer->toFile()->exists();
    }

    
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toFile()
     */
    public function toFile(): HTTPFile
    {
        return $this->writer->toFile();
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toString()
     */
    public function toString(): string
    {
        return $this->writer->toString();
    }
}


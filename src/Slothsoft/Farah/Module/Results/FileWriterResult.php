<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterFromFileTrait;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FileWriterResult extends ResultImplementation
{
    use DOMWriterFromFileTrait;

    private $writer;

    public function __construct(FarahUrl $url, FileWriterInterface $writer)
    {
        parent::__construct($url);
        
        $this->writer = $writer;
    }

    public function toFile(): HTTPFile
    {
        return $this->writer->toFile();
    }

    public function toString(): string
    {
        return $this->writer->toString();
    }

    public function exists(): bool
    {
        return $this->toFile()->exists();
    }
}


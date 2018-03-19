<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\FileWriterStringFromFileTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\ResultImplementation;

abstract class FileResult extends ResultImplementation
{
    use FileWriterStringFromFileTrait;
    
    private $file;
    
    public function __construct(FarahUrl $url, HTTPFile $file)
    {
        parent::__construct($url);
        
        $this->file = $file;
    }
    
    public function toFile(): HTTPFile
    {
        return $this->file;
    }
    
    public function exists(): bool
    {
        return $this->toFile()->exists();
    }
}


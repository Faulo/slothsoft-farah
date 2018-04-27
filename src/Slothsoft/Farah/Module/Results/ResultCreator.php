<?php
namespace Slothsoft\Farah\Module\Results;

use Psr\Http\Message\MessageInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Closure;

class ResultCreator
{
    private $ownerExecutable;
    private $type;
    public function __construct(ExecutableInterface $ownerExecutable, FarahUrlStreamIdentifier $type) {
        $this->ownerExecutable = $ownerExecutable;
        $this->type = $type;
    }
    
    public function createNullResult(): NullResult
    {
        $result = new NullResult();
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createDOMWriterResult(DOMWriterInterface $writer): DOMWriterResult
    {
        $result = new DOMWriterResult($writer);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createFileWriterResult(FileWriterInterface $writer): NullResult
    {
        $result = new NullResult($writer);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createFilePathResult(string $pathToFile): FilePathResult
    {
        $result = new FilePathResult($pathToFile);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createClosureResult(Closure $closure): NullResult
    {
        $result = new NullResult($closure);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createMessageResult(MessageInterface $message) : NullResult
    {
        $result = new NullResult($message);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
}


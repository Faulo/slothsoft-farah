<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Psr\Http\Message\MessageInterface;
use Slothsoft\Core\IO\HTTPStream;
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
    
    public function createNullResult(): ResultInterface
    {
        $result = new NullResult();
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createDOMWriterResult(DOMWriterInterface $writer): ResultInterface
    {
        $result = new DOMWriterResult($writer);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createFileWriterResult(FileWriterInterface $writer): ResultInterface
    {
        $result = new NullResult($writer);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createFilePathResult(string $pathToFile): ResultInterface
    {
        $result = new FilePathResult($pathToFile);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createClosureResult(Closure $closure): ResultInterface
    {
        $result = new NullResult($closure);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createMessageResult(MessageInterface $message) : ResultInterface
    {
        $result = new NullResult($message);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
    
    public function createHttpStreamResult(HTTPStream $stream) : ResultInterface {
        $result = new HttpStreamResult($stream);
        $result->init($this->ownerExecutable, $this->type);
        return $result;
    }
}


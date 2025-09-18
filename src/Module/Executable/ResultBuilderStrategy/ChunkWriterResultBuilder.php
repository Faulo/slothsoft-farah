<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ChunkWriterStreamBuilder;

class ChunkWriterResultBuilder implements ResultBuilderStrategyInterface {
    
    private $writer;
    
    private $fileName;
    
    private $isBufferable;
    
    public function __construct(ChunkWriterInterface $writer, string $fileName, bool $isBufferable = true) {
        $this->writer = $writer;
        $this->fileName = $fileName;
        $this->isBufferable = $isBufferable;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = new ChunkWriterStreamBuilder($this->writer, $this->fileName, $this->isBufferable);
        return new ResultStrategies($streamBuilder);
    }
}


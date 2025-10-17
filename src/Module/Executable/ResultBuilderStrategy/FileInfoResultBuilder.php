<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;
use SplFileInfo;

class FileInfoResultBuilder implements ResultBuilderStrategyInterface {
    
    private SplFileInfo $file;
    
    public function __construct(SplFileInfo $file) {
        $this->file = $file;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return false;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = new FileInfoStreamBuilder($this->file);
        return new ResultStrategies($streamBuilder);
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Ds\Map;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

class MapResultBuilder implements ResultBuilderStrategyInterface {
    
    private StreamBuilderStrategyInterface $defaultStream;
    
    private Map $streams;
    
    public function __construct(StreamBuilderStrategyInterface $proxy) {
        $this->defaultStream = $proxy;
        $this->streams = new Map();
    }
    
    public function addStreamBuilder(FarahUrlStreamIdentifier $type, StreamBuilderStrategyInterface $proxy) {
        $this->streams[$type] = $proxy;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return $this->streams->hasKey($type);
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        return new ResultStrategies($this->streams[$type] ?? $this->defaultStream);
    }
}


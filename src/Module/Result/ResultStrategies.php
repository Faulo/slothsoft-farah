<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

class ResultStrategies {
    
    public StreamBuilderStrategyInterface $streamBuilder;
    
    public function __construct(StreamBuilderStrategyInterface $streamBuilder) {
        $this->streamBuilder = $streamBuilder;
    }
}


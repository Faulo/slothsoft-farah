<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;

/**
 * Strategy bundle used when exposing a result through writer interfaces.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ResultStrategies {
    
    public StreamBuilderStrategyInterface $streamBuilder;
    
    public function __construct(StreamBuilderStrategyInterface $streamBuilder) {
        $this->streamBuilder = $streamBuilder;
    }
}


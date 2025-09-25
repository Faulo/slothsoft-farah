<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ProxyStreamBuilder;

class ProxyResultBuilder implements ResultBuilderStrategyInterface {
    
    private ResultInterface $proxy;
    
    public function __construct(ResultInterface $proxy) {
        $this->proxy = $proxy;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = new ProxyStreamBuilder($this->proxy);
        return new ResultStrategies($streamBuilder);
    }
}


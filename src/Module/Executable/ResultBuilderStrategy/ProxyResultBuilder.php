<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ProxyStreamBuilder;

class ProxyResultBuilder implements ResultBuilderStrategyInterface {
    
    private ExecutableInterface $proxy;
    
    private FarahUrlStreamIdentifier $defaultType;
    
    public function __construct(ExecutableInterface $proxy, ?FarahUrlStreamIdentifier $defaultType = null) {
        $this->proxy = $proxy;
        $this->defaultType = $defaultType ?? Executable::resultIsDefault();
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = new ProxyStreamBuilder($this->proxy->lookupResult($type === Executable::resultIsDefault() ? $this->defaultType : $type));
        return new ResultStrategies($streamBuilder);
    }
}


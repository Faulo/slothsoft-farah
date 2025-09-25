<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\InstructionStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\PathResolverStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy\ParameterSupplierStrategyInterface;

class AssetStrategies {
    
    public ExecutableBuilderStrategyInterface $executableBuilder;
    
    public PathResolverStrategyInterface $pathResolver;
    
    public ParameterFilterStrategyInterface $parameterFilter;
    
    public ParameterSupplierStrategyInterface $parameterSupplier;
    
    public InstructionStrategyInterface $instruction;
    
    public function __construct(ExecutableBuilderStrategyInterface $executableBuilder, PathResolverStrategyInterface $pathResolver, ParameterFilterStrategyInterface $parameterFilter, ParameterSupplierStrategyInterface $parameterSupplier, InstructionStrategyInterface $instruction) {
        $this->executableBuilder = $executableBuilder;
        $this->pathResolver = $pathResolver;
        $this->parameterFilter = $parameterFilter;
        $this->parameterSupplier = $parameterSupplier;
        $this->instruction = $instruction;
    }
}


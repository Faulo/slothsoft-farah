<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\InstructionStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\PathResolverStrategyInterface;

class AssetStrategies
{

    public $executableBuilder;

    public $pathResolver;

    public $parameterFilter;

    public $instruction;

    public function __construct(ExecutableBuilderStrategyInterface $executableBuilder, PathResolverStrategyInterface $pathResolver, ParameterFilterStrategyInterface $parameterFilter, InstructionStrategyInterface $instruction)
    {
        $this->executableBuilder = $executableBuilder;
        $this->pathResolver = $pathResolver;
        $this->parameterFilter = $parameterFilter;
        $this->instruction = $instruction;
    }
}


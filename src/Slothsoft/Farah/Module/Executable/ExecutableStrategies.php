<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;

class ExecutableStrategies
{

    /**
     *
     * @var ResultBuilderStrategyInterface
     */
    public $resultBuilder;

    public function __construct(ResultBuilderStrategyInterface $resultBuilder)
    {
        $this->resultBuilder = $resultBuilder;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;

class ProxyInstructionBuilder implements InstructionBuilderStrategyInterface {
    
    private ExecutableInterface $proxy;
    
    public function __construct(ExecutableInterface $proxy) {
        $this->proxy = $proxy;
    }
    
    public function buildUseInstructions(AssetInterface $context, FarahUrlArguments $args): UseInstructionCollection {
        return $this->proxy->lookupUseInstructions();
    }
    
    public function buildLinkInstructions(AssetInterface $context, FarahUrlArguments $args): LinkInstructionCollection {
        return $this->proxy->lookupLinkInstructions();
    }
}

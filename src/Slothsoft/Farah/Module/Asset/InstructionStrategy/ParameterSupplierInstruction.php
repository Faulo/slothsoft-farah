<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;

class ParameterSupplierInstruction extends NullInstruction
{

    public function isParameterSupplier(AssetInterface $context): bool
    {
        return true;
    }
}
<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

interface ParameterInstructionInterface
{

    public function getParameterName(): string;

    public function getParameterValue(): string;
}


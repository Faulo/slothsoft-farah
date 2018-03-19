<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces;

interface ParameterInstruction
{

    public function getParameterName(): string;

    public function getParameterValue(): string;
}


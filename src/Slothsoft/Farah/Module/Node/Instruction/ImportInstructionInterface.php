<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

interface ImportInstructionInterface
{
    public function getImportNodes(): array;
}


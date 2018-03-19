<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces;

interface ImportInstruction
{

    public function getImportNodes(): array;
}


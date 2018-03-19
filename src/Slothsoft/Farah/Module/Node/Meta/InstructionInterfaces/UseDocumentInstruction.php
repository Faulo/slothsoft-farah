<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

interface UseDocumentInstruction
{

    public function getReferencedDocumentAsset(): AssetInterface;

    public function getReferencedDocumentAlias(): string;
}


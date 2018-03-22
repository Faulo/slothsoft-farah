<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

interface UseDocumentInstructionInterface
{

    public function getReferencedDocumentAsset(): AssetInterface;

    public function getReferencedDocumentAlias(): string;
}


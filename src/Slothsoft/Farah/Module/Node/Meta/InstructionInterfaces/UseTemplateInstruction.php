<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

interface UseTemplateInstruction
{

    public function getReferencedTemplateAsset(): AssetInterface;
}


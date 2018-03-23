<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

interface UseManifestInstructionInterface
{

    public function getReferencedManifestAsset(): AssetInterface;
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\InstructionStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;

trait InstructionFromReferenceAttributeTrait
{

    public function getReferencedAsset(AssetInterface $context): AssetInterface
    {
        $ref = $context->getManifestElement()->getAttribute('ref');
        $url = FarahUrl::createFromReference($ref, $context->createUrl());
        return Module::resolveToAsset($url);
    }
}

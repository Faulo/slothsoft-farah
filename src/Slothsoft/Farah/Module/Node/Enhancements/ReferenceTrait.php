<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Enhancements;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

trait ReferenceTrait {

    private $referencedAsset;

    protected function getReference(): string
    {
        return $this->getElementAttribute(Module::ATTR_REFERENCE);
    }

    protected function getReferencedAsset(): AssetInterface
    {
        if ($this->referencedAsset === null) {
            $this->referencedAsset = FarahUrlResolver::resolveToAsset(FarahUrl::createFromReference($this->getReference(), $this->getOwnerModule()->getAuthority()));
        }
        return $this->referencedAsset;
    }
}


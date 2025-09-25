<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

trait LoadChildrenFromManifestTrait {
    
    public function loadChildren(AssetInterface $context): iterable {
        foreach ($context->getManifestElement()->getChildren() as $element) {
            yield $element->getAttribute(Manifest::ATTR_NAME);
        }
    }
}


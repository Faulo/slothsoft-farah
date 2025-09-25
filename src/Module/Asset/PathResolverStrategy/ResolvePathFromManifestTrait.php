<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;

trait ResolvePathFromManifestTrait {
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement {
        foreach ($context->getManifestElement()->getChildren() as $element) {
            if ($element->getAttribute(Manifest::ATTR_NAME) === $name) {
                return $element;
            }
        }
        throw new AssetPathNotFoundException($context, $name);
    }
}


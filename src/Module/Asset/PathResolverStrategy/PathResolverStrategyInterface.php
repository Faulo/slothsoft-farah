<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetInterface;

interface PathResolverStrategyInterface {
    
    /**
     * @return iterable<string>
     */
    public function loadChildren(AssetInterface $context): iterable;
    
    public function resolvePath(AssetInterface $context, string $name): LeanElement;
}


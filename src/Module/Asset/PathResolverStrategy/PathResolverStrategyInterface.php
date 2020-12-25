<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\PathResolverStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetInterface;

interface PathResolverStrategyInterface
{

    /**
     *
     * @param AssetInterface $context
     * @return LeanElement[]
     */
    public function loadChildren(AssetInterface $context): iterable;

    /**
     *
     * @param AssetInterface $context
     * @param string $name
     * @return LeanElement
     */
    public function resolvePath(AssetInterface $context, string $name): LeanElement;
}


<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\AssetBuilderStrategyInterface;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;

/**
 * Strategy bundle used when loading a Farah manifest and building assets.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class ManifestStrategies {
    
    public TreeLoaderStrategyInterface $treeLoader;
    
    public AssetBuilderStrategyInterface $assetBuilder;
    
    public function __construct(TreeLoaderStrategyInterface $treeLoader, AssetBuilderStrategyInterface $assetBuilder) {
        $this->treeLoader = $treeLoader;
        $this->assetBuilder = $assetBuilder;
    }
}


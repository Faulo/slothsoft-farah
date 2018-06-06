<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\TreeLoaderStrategyInterface;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\AssetBuilderStrategyInterface;

class ManifestStrategies
{

    public $treeLoader;

    public $assetBuilder;

    public function __construct(TreeLoaderStrategyInterface $treeLoader, AssetBuilderStrategyInterface $assetBuilder)
    {
        $this->treeLoader = $treeLoader;
        $this->assetBuilder = $assetBuilder;
    }
}


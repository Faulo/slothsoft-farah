<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;

interface TreeLoaderStrategyInterface
{
    public function loadTree(?ManifestInterface $context, string $manifestDirectory): LeanElement;
}


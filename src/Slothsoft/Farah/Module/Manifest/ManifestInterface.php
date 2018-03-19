<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;

interface ManifestInterface
{

    public function getRootElement(): LeanElement;
}


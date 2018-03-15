<?php
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;

interface ManifestInterface
{
    public function getRootElement() : LeanElement;
}


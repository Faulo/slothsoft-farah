<?php
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;

abstract class ManifestImplementation implements ManifestInterface
{
    private $rootElement;
    public function getRootElement() : LeanElement
    {
        if ($this->rootElement === null) {
            $this->rootElement = $this->loadRootElement();
        }
        return $this->rootElement;
    }
    abstract protected function loadRootElement() : LeanElement;
}


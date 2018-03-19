<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;

class XmlManifest extends ManifestImplementation
{

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        assert(is_file($this->path), "Missing manifest file: {$path}");
    }

    protected function loadRootElement(): LeanElement
    {
        $dom = new DOMHelper();
        return LeanElement::createTreeFromDOMDocument($dom->loadDocument($this->path));
    }
}


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
    }

    public function getPath(): string
    {
        return $this->path;
    }

    protected function loadRootElement(): LeanElement
    {
        $dom = new DOMHelper();
        return LeanElement::createTreeFromDOMDocument($dom->loadDocument($this->path));
    }
}


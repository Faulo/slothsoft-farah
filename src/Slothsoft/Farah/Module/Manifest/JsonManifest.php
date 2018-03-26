<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest;

use Slothsoft\Core\XML\LeanElement;

class JsonManifest extends ManifestImplementation
{

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    protected function loadRootElement(): LeanElement
    {
        $data = json_decode(file_get_contents($this->path), true);
        // TODO
        return LeanElement::createOneFromArray('assets', []);
    }
}


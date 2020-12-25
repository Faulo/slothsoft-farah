<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Core\XML\LeanElement;

class ManifestElementBuilder {

    public static function createResource(string $name, string $path, string $type): LeanElement {
        return LeanElement::createOneFromArray('resource', [
            'name' => $name,
            'path' => $path,
            'type' => $type
        ]);
    }

    public static function createResourceDirectory(string $name, string $path, string $type): LeanElement {
        return LeanElement::createOneFromArray('resource-directory', [
            'name' => $name,
            'path' => $path,
            'type' => $type
        ]);
    }
}


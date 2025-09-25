<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Manifest\Manifest;

class ManifestElementBuilder {
    
    public static function createResource(string $name, string $path, string $type): LeanElement {
        return LeanElement::createOneFromArray(Manifest::TAG_RESOURCE, [
            Manifest::ATTR_NAME => $name,
            Manifest::ATTR_PATH => $path,
            Manifest::ATTR_TYPE => $type
        ]);
    }
    
    public static function createResourceDirectory(string $name, string $path, string $type): LeanElement {
        return LeanElement::createOneFromArray(Manifest::TAG_RESOURCE_DIRECTORY, [
            Manifest::ATTR_NAME => $name,
            Manifest::ATTR_PATH => $path,
            Manifest::ATTR_TYPE => $type
        ]);
    }
}


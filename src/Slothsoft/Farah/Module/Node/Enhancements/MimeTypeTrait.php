<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Enhancements;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetInterface;

trait MimeTypeTrait {

    protected function getMimeType(): string
    {
        return $this->getElementAttribute(Module::ATTR_TYPE, '*/*');
    }

    public function createChildResourceAsset(string $name, string $path): PhysicalAssetInterface
    {
        if (is_dir($this->getRealPath() . DIRECTORY_SEPARATOR . $path)) {
            $element = LeanElement::createOneFromArray(Module::TAG_RESOURCE_DIRECTORY, [
                Module::ATTR_NAME => $name,
                Module::ATTR_PATH => $name,
                Module::ATTR_TYPE => $this->getMimeType()
            ]);
        } else {
            $element = LeanElement::createOneFromArray(Module::TAG_RESOURCE, [
                Module::ATTR_NAME => $name,
                Module::ATTR_TYPE => $this->getMimeType()
            ]);
        }
        return $this->createChildNode($element);
    }
}


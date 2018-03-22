<?php
namespace Slothsoft\Farah\Module\Node\Enhancements;

use Slothsoft\Farah\Module\Module;

trait MimeTypeTrait {
    protected function getMimeType(): string
    {
        return $this->getElementAttribute(Module::ATTR_TYPE, '*/*');
    }
}


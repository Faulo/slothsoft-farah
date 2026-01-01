<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Configuration;

use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use InvalidArgumentException;

final class FarahUrlConfigurationField extends ConfigurationField {
    
    public function getValue() {
        $value = parent::getValue();
        return $value instanceof FarahUrl ? $value : $this->loadValue($value);
    }
    
    private function loadValue($newValue): FarahUrl {
        if (is_string($newValue)) {
            $newValue = FarahUrl::createFromReference($newValue);
        }
        if (! ($newValue instanceof FarahUrl)) {
            throw new InvalidArgumentException("Value must be a valid asset reference: $newValue");
        }
        parent::setValue($newValue);
        return $newValue;
    }
}


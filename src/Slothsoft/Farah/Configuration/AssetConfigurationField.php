<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Configuration;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use InvalidArgumentException;
use Slothsoft\Core\Configuration\ConfigurationField;

class AssetConfigurationField extends ConfigurationField
{

    public function getValue()
    {
        $value = parent::getValue();
        return $value instanceof AssetInterface ? $value : $this->loadValue($value);
    }

    private function loadValue($newValue): AssetInterface
    {
        if (is_string($newValue)) {
            $newValue = FarahUrl::createFromReference($newValue);
        }
        if ($newValue instanceof FarahUrl) {
            $newValue = FarahUrlResolver::resolveToAsset($newValue);
        }
        if (! ($newValue instanceof AssetInterface)) {
            throw new InvalidArgumentException("Value must be a valid asset reference: $newValue");
        }
        parent::setValue($newValue);
        return $newValue;
    }
}


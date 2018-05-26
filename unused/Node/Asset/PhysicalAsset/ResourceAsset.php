<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\Executables\ExecutableCreator;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Enhancements\MimeTypeTrait;
use Slothsoft\Farah\Module\ParameterFilters\DenyAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;

class ResourceAsset extends PhysicalAssetBase
{
    use MimeTypeTrait;

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return new DenyAllFilter();
    }

    protected function loadExecutable(FarahUrlArguments $args): ExecutableInterface
    {
        $creator = new ExecutableCreator($this, $args);
        $type = $this->getMimeType();
        switch ($type) {
            case 'text/html':
                return $creator->createHtmlFile($this->getRealPath());
            case 'application/xml':
                return $creator->createXmlFile($this->getRealPath());
            default:
                if (substr($type, - 4) === '+xml') {
                    return $creator->createXmlFile($this->getRealPath());
                } else {
                    return $creator->createBinaryFile($this->getRealPath());
                }
        }
    }
}


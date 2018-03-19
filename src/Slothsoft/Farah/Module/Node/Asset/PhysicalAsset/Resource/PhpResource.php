<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhpResource extends ResourceImplementation
{

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return new AllowAllFilter();
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return ResultCatalog::createFromMixed($url, $this->includePhpFile());
    }

    private function includePhpFile()
    {
        assert($this->toFile()->exists());
        return include ($this->getRealPath());
    }
}


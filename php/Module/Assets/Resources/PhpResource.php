<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets\Resources;

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
class PhpResource extends GenericResource
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
        return include($this->getRealPath());
    }
}


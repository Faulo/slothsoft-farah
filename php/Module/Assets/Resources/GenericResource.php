<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Farah\Module\Assets\GenericAsset;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\ParameterFilters\DenyAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\Results\BinaryFileResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericResource extends GenericAsset
{
    public function getType() : string {
        return $this->getElementAttribute('type');
    }

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return new DenyAllFilter();
    }
    
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new BinaryFileResult($url, $this->getRealPath());
    }
}


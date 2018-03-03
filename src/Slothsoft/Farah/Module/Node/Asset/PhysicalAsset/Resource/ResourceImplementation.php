<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\PhysicalAssetImplementation;
use Slothsoft\Farah\Module\ParameterFilters\DenyAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\Results\BinaryFileResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceImplementation extends PhysicalAssetImplementation implements ResourceInterface
{

    public function getType(): string
    {
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


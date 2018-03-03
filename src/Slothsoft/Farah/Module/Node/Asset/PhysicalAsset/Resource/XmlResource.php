<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\DOMFileResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class XmlResource extends ResourceImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new DOMFileResult($url, $this->getRealPath());
    }
}


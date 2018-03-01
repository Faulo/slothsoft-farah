<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\DOMFileResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class XmlResource extends GenericResource
{
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new DOMFileResult($url, $this->getRealPath());
    }
}


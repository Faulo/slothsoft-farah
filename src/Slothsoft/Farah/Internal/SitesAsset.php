<?php
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\DOMDocumentResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SitesAsset extends AssetImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new DOMDocumentResult($url, Kernel::getInstance()->getSitesDocument());
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\ResultCatalog;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestAsset extends AssetImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return ResultCatalog::createDOMWriterResult($url, Kernel::getInstance()->getRequest());
    }
}


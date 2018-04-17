<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestAsset extends AssetImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        try {
            return ResultCatalog::createMessageResult($url, Kernel::getCurrentRequest());
        } catch (ConfigurationRequiredException $e) {
            return ResultCatalog::createNullResult($url);
        }
    }
}


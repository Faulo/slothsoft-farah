<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\ResultCatalog;
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
        try {
            return Kernel::getCurrentSitemap()->createResult($url->getArguments());
        } catch (ConfigurationRequiredException $e) {
            return ResultCatalog::createNullResult($url);
        }
    }
}


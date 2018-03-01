<?php
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Controllers\GenericController;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\DOMWriterResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestController extends GenericController
{
    public function createResult(FarahUrl $url) : ResultInterface{
        return new DOMWriterResult($url, Kernel::getInstance()->getRequest());
    }
    public function createPathResolver() : PathResolverInterface {
        return PathResolverCatalog::createCatchAllPathResolver($this->getAsset());
    }
}


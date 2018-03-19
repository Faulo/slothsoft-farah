<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryAsset extends DirectoryAsset
{

    protected function loadPathResolver(): PathResolverInterface
    {
        return PathResolverCatalog::createResourceDirectoryPathResolver($this);
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return ResultCatalog::createDOMWriterResult($url, $this);
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\PathResolvers\ResourceDirectoryPathResolver;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryDefinition extends ContainerDefinition
{

    protected function loadPathResolver(): PathResolverInterface
    {
        return new ResourceDirectoryPathResolver($this);
    }
}


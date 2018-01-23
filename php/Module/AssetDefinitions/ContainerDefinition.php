<?php
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\Module\PathResolvers\MapPathResolver;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ContainerDefinition extends AssetDefinition
{
    protected function loadPathResolver() : PathResolverInterface {
        $map = [];
        $map['/'] = $this;
        foreach ($this->getChildren() as $definition) {
            $name = $definition->getName();
            if ($name !== '/') {
                $map["/$name"] = $definition;
            }
        }
        return new MapPathResolver($this, $map);
    }
}


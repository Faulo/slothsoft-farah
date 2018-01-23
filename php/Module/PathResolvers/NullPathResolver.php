<?php
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class NullPathResolver implements PathResolverInterface
{
    private $definition;
    public function __construct(AssetDefinition $definition) {
        $this->definition = $definition;
    }
    public function resolvePath(string $path) : AssetDefinition
    {
        if ($path === '/') {
            return $this->definition;
        }
    }
    public function getPathMap() : array {
        return ['/' => $this->definition];
    }
}


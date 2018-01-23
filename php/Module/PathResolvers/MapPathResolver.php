<?php
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MapPathResolver implements PathResolverInterface
{
    private $definition;
    private $definitionMap;
    
    public function __construct(AssetDefinition $definition, array $definitionMap)
    {
        $this->definition = $definition;
        $this->definitionMap = $definitionMap;
    }
    public function resolvePath(string $path) : AssetDefinition
    {
        if (isset($this->definitionMap[$path])) {
            return $this->definitionMap[$path];
        } else {
            assert(preg_match('~^/[^/]+~', $path, $match), "invalid asset path: $path");
            
            $childPath = $match[0];
            $descendantPath = substr($path, strlen($childPath));
            
            if (isset($this->definitionMap[$childPath])) {
                return $descendantPath === ''
                    ? $this->definitionMap[$childPath]
                    : $this->definitionMap[$childPath]->traverseTo($descendantPath);
            }
        }
        throw new RuntimeException("Asset {$this->definition->getAssetPath()} did not provide a closure for $path!");
    }
    
    public function getPathMap() : array {
        return $this->definitionMap;
    }
}


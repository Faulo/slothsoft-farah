<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MapPathResolver implements PathResolverInterface
{

    private $definition;

    private $definitionMap;

    public function __construct(AssetDefinitionInterface $definition, array $definitionMap)
    {
        $this->definition = $definition;
        $this->definitionMap = $definitionMap;
    }

    public function resolvePath(string $path): AssetDefinitionInterface
    {
        if (isset($this->definitionMap[$path])) {
            return $this->definitionMap[$path];
        } else {
            assert(preg_match('~^/[^/]+~', $path, $match), "Invalid asset path: $path");
            
            $childPath = $match[0];
            $descendantPath = substr($path, strlen($childPath));
            
            if (isset($this->definitionMap[$childPath])) {
                return $descendantPath === '' ? $this->definitionMap[$childPath] : $this->definitionMap[$childPath]->traverseTo($descendantPath);
            }
        }
        throw ExceptionContext::append(new OutOfRangeException("Asset {$this->definition->getId()} did not provide a mapping for $path!"), [
            'definition' => $this->definition
        ]);
    }

    public function getPathMap(): array
    {
        return $this->definitionMap;
    }
}


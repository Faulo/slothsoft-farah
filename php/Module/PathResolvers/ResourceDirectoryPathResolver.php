<?php
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{
    private $definition;
    
    public function __construct(AssetDefinition $definition) {
        $this->definition = $definition;
    }
    public function resolvePath(string $path) : AssetDefinition
    {
        if ($path === '/') {
            $ret = $this->definition;
        } else {
            assert(preg_match('~^/([^/]+)~', $path, $match), "invalid asset path: $path");
            
            $childPath = $match[0];
            $childName = $match[1];
            $descendantPath = substr($path, strlen($childPath));
            
            if ($descendantPath === '') {
                $data = [];
                $data['name'] = $childName;
                $data['type'] = $this->definition->getAttribute('type');
                
                $ret = AssetDefinition::createFromArray($this->definition->getOwnerModule(), Module::TAG_RESOURCE, $data, $this->definition);
            } else {
                $ret = $this->definition->traverseTo($childPath)->traverseTo($descendantPath);
            }
        }
        return $ret;
    }
    public function getPathMap() : array {
        //todo: FileSystem::scanDir or somesuch
        return ['/' => $this->definition];
    }
}


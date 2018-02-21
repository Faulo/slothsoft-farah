<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Module\DefinitionFactory;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResourceDirectoryPathResolver implements PathResolverInterface
{

    private $definition;

    public function __construct(AssetDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    public function resolvePath(string $path): AssetDefinitionInterface
    {
        if ($path === '/') {
            $ret = $this->definition;
        } else {
            assert(preg_match('~^/([^/]+)~', $path, $match), "Invalid asset path: $path");
            
            $childPath = $match[0];
            $childName = $match[1];
            $descendantPath = substr($path, strlen($childPath));
            
            if ($descendantPath === '') {
                $data = [];
                $data['name'] = $childName;
                $data['type'] = $this->definition->getAttribute('type');
                
                $ret = DefinitionFactory::createFromArray($this->definition->getOwnerModule(), Module::TAG_RESOURCE, $data, $this->definition);
            } else {
                $ret = $this->definition->traverseTo($childPath)->traverseTo($descendantPath);
            }
        }
        return $ret;
    }

    public function getPathMap(): array
    {
        $ret = [
            '/' => $this->definition
        ];
        $mime = $this->definition->hasAttribute('type') ? $this->definition->getAttribute('type') : '*/*';
        $path = $this->definition->getRealPath();
        assert(is_dir($path), "Path is not a directory: $path");
        $fileList = FileSystem::scanDir($path);
        foreach ($fileList as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (MimeTypeDictionary::matchesMime($ext, $mime)) {
                $ret["/$name"] = $this->resolvePath("/$name");
            }
        }
        
        return $ret;
    }
}


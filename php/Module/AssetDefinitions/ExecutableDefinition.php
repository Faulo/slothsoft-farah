<?php
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\HTTPClosure;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\PathResolvers\MapPathResolver;
use Slothsoft\Farah\Module\PathResolvers\NullPathResolver;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Closure;
use DOMDocument;
use UnexpectedValueException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExecutableDefinition extends AssetDefinition implements ClosurableDefinition
{
    /*
    private $childMap;
    public function traverseTo(string $path) : AssetDefinition {
        if ($path === '/') {
            return $this;
        }
        if ($this->childMap === null) {
            $this->childMap = $this->loadChildMap();
        }
        if (!isset($this->childMap[$path])) {
            throw new RuntimeException("Executable file {$this->getAssetPath()} did not provide a closure for $path!");
        }
        return $this->childMap[$path];
    }
    //*/
    protected function loadPathResolver() : PathResolverInterface {
        $res = $this->executePath();
        if ($res instanceof PathResolverInterface) {
            //my work here is done
            $ret = $res;
        } elseif ($res instanceof Closure) {
            $ret = new NullPathResolver($this->createClosure([], $res));
        } else {
            //let's get $res into a PathResolverInterface somehow
            $ret = [];
            if (!is_array($res)) {
                $res = ['/' => $res];
            }
            foreach ($res as $path => $asset) {
                switch (true) {
                    case $asset instanceof AssetDefinition:
                        break;
                    case $asset instanceof Closure:
                        $asset = $this->createClosure([], $asset);
                        break;
                    default: throw new UnexpectedValueException("{$this->getName()} must return closurable... stuff!");
                }
                $ret[$path] = $asset;
            }
            
            $ret = new MapPathResolver($this, $ret);
        }
        return $ret;
    }
    
    public function createClosure(array $options, Closure $closure) : ClosureDefinition {
        $definition = self::createFromArray($this->getOwnerModule(), Module::TAG_CLOSURE, [], $this);
        $definition->setClosure(new HTTPClosure($options, $closure));
        return $definition;
    }
    
    public function getClosure() : HTTPClosure {
        $childMap = $this->getPathResolver()->getPathMap();
        if (isset($childMap['/']) and $childMap['/'] instanceof ClosurableDefinition) {
            return $childMap['/']->getClosure();
        }
        $options = [];
        $definition = $this;
        return new HTTPClosure(
            $options, 
            function() use ($definition, $childMap) {
                $resultDoc = new DOMDocument();
                $resultDoc->appendChild($definition->toNode($resultDoc));
                foreach ($childMap as $path => $childDefinition) {
                    $childNode = $childDefinition->toNode($resultDoc);
                    $childNode->setAttribute('uri', $path);
                    $resultDoc->documentElement->appendChild($childNode);
                }
                return $resultDoc;
            }
        );
    }
    
    private function executePath() {
        assert(is_file($this->getRealPath()), "not an executable file: {$this->getRealPath()}");
        
        return include($this->getRealPath());
    }
}


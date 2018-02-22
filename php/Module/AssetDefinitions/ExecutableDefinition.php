<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\HTTPClosure;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\PathResolvers\MapPathResolver;
use Slothsoft\Farah\Module\PathResolvers\NullPathResolver;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use BadFunctionCallException;
use Closure;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExecutableDefinition extends GenericAssetDefinition implements ClosurableInterface
{

    protected function loadPathResolver(): PathResolverInterface
    {
        $res = $this->executePath();
        if ($res instanceof PathResolverInterface) {
            // my work here is done
            $ret = $res;
        } elseif ($res instanceof Closure) {
            $ret = new NullPathResolver($this->createClosure([], $res));
        } else {
            // let's get $res into a PathResolverInterface somehow
            $ret = [];
            if (! is_array($res)) {
                $res = [
                    '/' => $res
                ];
            }
            $offset = strlen($this->getAssetPath());
            foreach ($res as $path => $asset) {
                switch (true) {
                    case $asset instanceof AssetDefinitionInterface:
                        break;
                    case $asset instanceof Closure:
                        $asset = $this->createClosure([
                            'path' => $path
                        ], $asset);
                        break;
                    default:
                        throw ExceptionContext::append(new BadFunctionCallException("ExecutableDefinition {$this->getId()} must return closurable... stuff!"), [
                            'definition' => $this
                        ]);
                }
                $ret[substr($asset->getAssetPath(), $offset)] = $asset;
            }
            $ret = new MapPathResolver($this, $ret);
        }
        return $ret;
    }

    public function createClosure(array $options, Closure $closure): ClosureDefinition
    {
        $attributes = [];
        $attributes['realpath'] = $this->getRealPath() . ($options['path'] ?? '/');
        $attributes['assetpath'] = $this->getAssetPath() . ($options['path'] ?? '/');
        $definition = $this->createChildDefinition(LeanElement::createOneFromArray(Module::TAG_CLOSURE, $attributes));
        $definition->setClosure(new HTTPClosure($options, $closure));
        return $definition;
    }

    public function getClosure(): HTTPClosure
    {
        $childMap = $this->getPathResolver()->getPathMap();
        if (isset($childMap['/']) and $childMap['/'] instanceof ClosurableInterface) {
            return $childMap['/']->getClosure();
        }
        $options = [];
        $definition = $this;
        return new HTTPClosure($options, function (AssetInterface $asset) use ($definition, $childMap) {
            $resultDoc = new DOMDocument();
            $resultDoc->appendChild($asset->toDefinitionElement($resultDoc));
            $repository = AssetRepository::getInstance();
            foreach ($childMap as $path => $childDefinition) {
                $childAsset = $repository->lookupAssetByUrl($childDefinition->toUrl($asset->getArguments()));
                if ($childAsset instanceof DOMWriterInterface) {
                    $childNode = $childAsset->toElement($resultDoc);
                } else {
                    $childNode = $childAsset->toDefinitionElement($resultDoc);
                }
                $resultDoc->documentElement->appendChild($childNode);
            }
            return $resultDoc;
        });
    }

    private function executePath()
    {
        assert(is_file($this->getRealPath()), "File {$this->getRealPath()} appears to be missing.");
        
        return include ($this->getRealPath());
    }
}


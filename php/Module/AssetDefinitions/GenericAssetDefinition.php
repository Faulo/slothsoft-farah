<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\NullPathResolver;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericAssetDefinition implements AssetDefinitionInterface
{

    private $ownerModule;

    private $tag;

    private $attributes;

    private $children;

    private $pathResolver;

    private $parameterFilter;

    public function init(Module $ownerModule, string $tag, array $attributes)
    {
        $this->ownerModule = $ownerModule;
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = [];
        
        // echo "loading definition {$this->getId()}" . PHP_EOL;
    }

    public function getOwnerModule(): Module
    {
        return $this->ownerModule;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getId(): string
    {
        return $this->ownerModule->getId() . $this->getAssetPath();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getName(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function getRealPath(): string
    {
        return $this->attributes['realpath'] ?? '';
    }

    public function getAssetPath(): string
    {
        return $this->attributes['assetpath'] ?? '';
    }

    public function getAttribute(string $key): string
    {
        return $this->attributes[$key] ?? '';
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    public function setAttribute(string $key, string $val) {
        $this->attributes[$key] = $val;
    }

    public function appendChild(AssetDefinitionInterface $asset)
    {
        $this->children[] = $asset;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function traverseTo(string $path): AssetDefinitionInterface
    {
        return $this->getPathResolver()->resolvePath($path);
    }

    public function getPathResolver(): PathResolverInterface
    {
        if ($this->pathResolver === null) {
            $this->pathResolver = $this->loadPathResolver();
        }
        return $this->pathResolver;
    }

    protected function loadPathResolver(): PathResolverInterface
    {
        return new NullPathResolver($this);
    }

    public function filterParameters(array &$args): bool
    {
        $ret = false;
        $filter = $this->getParameterFilter();
        foreach ($args as $key => $val) {
            if (! $filter->isAllowedName($key)) {
                unset($args[$key]);
                $ret = true;
            }
        }
        return $ret;
    }

    public function getParameterFilter(): ParameterFilterInterface
    {
        if ($this->parameterFilter === null) {
            $this->parameterFilter = $this->loadParameterFilter();
        }
        return $this->parameterFilter;
    }

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return new AllowAllFilter();
    }

    public function toUrl(array $args): FarahUrl
    {
        return FarahUrl::createFromReference($this->getAssetPath(), $this->getOwnerModule(), $args);
    }
}


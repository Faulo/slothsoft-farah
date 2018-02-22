<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Core\XML\LeanElement;
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

    private $element;

    private $children;

    private $pathResolver;

    private $parameterFilter;

    public function init(Module $ownerModule, LeanElement $element, array $children)
    {
        $this->ownerModule = $ownerModule;
        $this->element = $element;
        $this->children = $children;
    }

    public function getOwnerModule(): Module
    {
        return $this->ownerModule;
    }

    public function getElement(): LeanElement
    {
        return $this->element;
    }

    public function getElementTag(): string
    {
        return $this->element->getTag();
    }

    public function getElementAttribute(string $key, string $default = null): string
    {
        return $this->element->getAttribute($key, $default);
    }

    public function hasElementAttribute(string $key): bool
    {
        return $this->element->hasAttribute($key);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function createChildDefinition(LeanElement $element): AssetDefinitionInterface
    {
        return $this->ownerModule->getDefinitionFactory()->createDefinition($element, $this->element);
    }

    public function getId(): string
    {
        return $this->ownerModule->getId() . $this->getAssetPath();
    }

    public function getName(): string
    {
        return $this->getElementAttribute(Module::ATTR_NAME);
    }

    public function getPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_PATH);
    }

    public function getRealPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_REALPATH);
    }

    public function getAssetPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_ASSETPATH);
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


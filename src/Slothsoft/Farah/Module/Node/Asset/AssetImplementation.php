<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\ModuleNodeImplementation;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetImplementation extends ModuleNodeImplementation implements AssetInterface, UseDocumentInstructionInterface, UseTemplateInstructionInterface, UseManifestInstructionInterface, UseStylesheetInstructionInterface, UseScriptInstructionInterface
{

    private $path;

    private $resultList = [];

    private $pathResolver;

    private $parameterFilter;

    private $instructionCollector;

    private $linkedStylesheets;

    private $linkedScripts;

    public function getUrlPath(): FarahUrlPath
    {
        if ($this->path === null) {
            $this->path = FarahUrlPath::createFromString($this->getAssetPath());
        }
        return $this->path;
    }

    public function getId(): string
    {
        return $this->getOwnerModule()->getId() . $this->getAssetPath();
    }

    public function getName(): string
    {
        return $this->getElementAttribute(Module::ATTR_NAME);
    }

    public function getAssetPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_ASSETPATH);
    }

    public function traverseTo(string $path): AssetInterface
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
        return PathResolverCatalog::createNullPathResolver($this);
    }

    public function applyParameterFilter(FarahUrlArguments $args): FarahUrlArguments
    {
        $valueList = $args->getValueList();
        $hasChanged = false;
        $filter = $this->getParameterFilter();
        foreach ($valueList as $name => $value) {
            if (! $filter->isAllowedName($name)) {
                unset($valueList[$name]);
                $hasChanged = true;
            }
        }
        foreach ($filter->getDefaultMap() as $name => $value) {
            if (! isset($valueList[$name])) {
                $valueList[$name] = $value;
                $hasChanged = true;
            }
        }
        return $hasChanged ? FarahUrlArguments::createFromValueList($valueList) : $args;
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

    public function createUrl(FarahUrlArguments $args = null): FarahUrl
    {
        return $this->getOwnerModule()->createUrl($this->getUrlPath(), $args ?? FarahUrlArguments::createEmpty());
    }

    public function createResult(FarahUrlArguments $args = null): ResultInterface
    {
        $args = $this->mergeWithManifestArguments($args ?? FarahUrlArguments::createEmpty());
        $args = $this->applyParameterFilter($args);
        $id = (string) $args;
        if (! isset($this->resultList[$id])) {
            $this->resultList[$id] = $this->loadResult($this->createUrl($args));
        }
        return $this->resultList[$id];
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getInstructionCollector(): InstructionCollector
    {
        if ($this->instructionCollector === null) {
            $this->instructionCollector = $this->loadInstructionCollector();
        }
        return $this->instructionCollector;
    }

    protected function loadInstructionCollector(): InstructionCollector
    {
        return InstructionCollector::createFromAsset($this);
    }

    public function lookupLinkedStylesheets(): array
    {
        return $this->getInstructionCollector()->stylesheetAssets;
    }

    public function lookupLinkedScripts(): array
    {
        return $this->getInstructionCollector()->scriptAssets;
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return ResultCatalog::createTransformationResult($url, $this->getName(), $this->getInstructionCollector());
    }

    public function getReferencedDocumentAsset(): AssetInterface
    {
        return $this;
    }

    public function getReferencedDocumentAlias(): string
    {
        return $this->getName();
    }

    public function getReferencedManifestAsset(): AssetInterface
    {
        return $this;
    }

    public function getReferencedTemplateAsset(): AssetInterface
    {
        return $this;
    }

    public function getReferencedStylesheetAsset(): AssetInterface
    {
        return $this;
    }

    public function getReferencedScriptAsset(): AssetInterface
    {
        return $this;
    }

    public function isUseDocument(): bool
    {
        return strpos($this->getElementAttribute(Module::ATTR_USE), Module::ATTR_USE_DOCUMENT) !== false;
    }

    public function isUseManifest(): bool
    {
        return strpos($this->getElementAttribute(Module::ATTR_USE), Module::ATTR_USE_MANIFEST) !== false;
    }

    public function isUseTemplate(): bool
    {
        return strpos($this->getElementAttribute(Module::ATTR_USE), Module::ATTR_USE_TEMPLATE) !== false;
    }

    public function isUseStylesheet(): bool
    {
        return strpos($this->getElementAttribute(Module::ATTR_USE), Module::ATTR_USE_STYLESHEET) !== false;
    }

    public function isUseScript(): bool
    {
        return strpos($this->getElementAttribute(Module::ATTR_USE), Module::ATTR_USE_SCRIPT) !== false;
    }
}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executables\ExecutableCreator;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\ModuleNodeBase;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class AssetBase extends ModuleNodeBase implements AssetInterface, UseDocumentInstructionInterface, UseTemplateInstructionInterface, UseManifestInstructionInterface, UseStylesheetInstructionInterface, UseScriptInstructionInterface
{

    private $executables = [];

    private $pathResolver;

    private $parameterFilter;

    private $instructionCollector;
    
    private $assetChildren;
    
    private $manifestArguments;
    
    public final function __toString(): string
    {
        return $this->getId();
    }
    
    public final function getId(): string
    {
        return $this->getElementAttribute(Module::ATTR_ID);
    }

    public final function getName(): string
    {
        return $this->getElementAttribute(Module::ATTR_NAME);
    }

    public final function getAssetPath(): string
    {
        return $this->getElementAttribute(Module::ATTR_ASSETPATH);
    }
    
    public final function getUse(): string
    {
        return $this->getElementAttribute(Module::ATTR_USE);
    }
    
    public final function getAssetChildren(): array
    {
        if ($this->assetChildren === null) {
            $this->assetChildren = array_filter(
                $this->getChildren(),
                function (ModuleNodeInterface $node) {
                    return $node instanceof AssetInterface;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        return $this->assetChildren;
    }
    
    
    public final function traverseTo(string $path): AssetInterface
    {
        return $this->getPathResolver()->resolvePath($path);
    }
    protected final function getPathResolver(): PathResolverInterface
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

    
    public final function applyParameterFilter(FarahUrlArguments $args): FarahUrlArguments
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
    protected final function getParameterFilter(): ParameterFilterInterface
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

    
    public final function createUrl($args = null, $fragment = null): FarahUrl
    {
        return $this->getOwnerModule()->createUrl($this->getAssetPath(), $args, $fragment);
    }

    public final function lookupExecutable(FarahUrlArguments $args = null): ExecutableInterface
    {
        $args = $args
            ? FarahUrlArguments::createFromMany($args, $this->getManifestArguments())
            : $this->getManifestArguments();
        $args = $this->applyParameterFilter($args);
        $id = (string) $args;
        if (! isset($this->executables[$id])) {
            $this->executables[$id] = $this->loadExecutable($args);
        }
        return $this->executables[$id];
    }
    
    protected function loadExecutable(FarahUrlArguments $args): ExecutableInterface
    {
        $resultCreator = new ExecutableCreator($this, $args);
        return $resultCreator->createTransformationExecutable($this->getName(), $this->collectInstructions());
    }
    
    protected final function getManifestArguments(): FarahUrlArguments
    {
        if ($this->manifestArguments === null) {
            $this->manifestArguments = $this->loadManifestArguments();
        }
        return $this->manifestArguments;
    }
    
    protected function loadManifestArguments(): FarahUrlArguments
    {
        $data = [];
        foreach ($this->getChildren() as $child) {
            if ($child->isParameter()) {
                $data[$child->getParameterName()] = $child->getParameterValue();
            }
        }
        return FarahUrlArguments::createFromValueList($data);
    }

    public final function collectInstructions(): InstructionCollector
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
    
    public function isImport() : bool {
        return false;
    }
    
    public function isParameter() : bool {
        return false;
    }

    public function isUseDocument(): bool
    {
        return strpos($this->getUse(), Module::ATTR_USE_DOCUMENT) !== false;
    }

    public function isUseManifest(): bool
    {
        return strpos($this->getUse(), Module::ATTR_USE_MANIFEST) !== false;
    }

    public function isUseTemplate(): bool
    {
        return strpos($this->getUse(), Module::ATTR_USE_TEMPLATE) !== false;
    }

    public function isUseStylesheet(): bool
    {
        return strpos($this->getUse(), Module::ATTR_USE_STYLESHEET) !== false;
    }

    public function isUseScript(): bool
    {
        return strpos($this->getUse(), Module::ATTR_USE_SCRIPT) !== false;
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
}


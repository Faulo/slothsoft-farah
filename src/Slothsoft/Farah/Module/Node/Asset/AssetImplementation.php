<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Node\ModuleNodeImplementation;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\Node\Meta\MetaInterface;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\LinkScriptInstruction;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\LinkStylesheetInstruction;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\NullResult;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetImplementation extends ModuleNodeImplementation implements AssetInterface
{
    use DOMWriterDocumentFromElementTrait;

    private $path;

    private $resultList = [];

    private $pathResolver;

    private $parameterFilter;

    private $assetChildren;

    private $metaChildren;

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

    public function getAssetChildren(): array
    {
        if ($this->assetChildren === null) {
            $this->assetChildren = array_filter($this->getChildren(), function (ModuleNodeInterface $node) {
                return $node instanceof AssetInterface;
            }, ARRAY_FILTER_USE_BOTH);
        }
        return $this->assetChildren;
    }

    public function getMetaChildren(): array
    {
        if ($this->metaChildren === null) {
            $this->metaChildren = array_filter($this->getChildren(), function (ModuleNodeInterface $node) {
                return $node instanceof MetaInterface;
            }, ARRAY_FILTER_USE_BOTH);
        }
        return $this->metaChildren;
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

    public function createUrl(FarahUrlArguments $args): FarahUrl
    {
        return $this->getOwnerModule()->createUrl($this->getUrlPath(), $args);
    }

    public function lookupResultByArguments(FarahUrlArguments $args): ResultInterface
    {
        $args = $this->mergeWithManifestArguments($args);
        $args = $this->applyParameterFilter($args);
        $id = (string) $args;
        if (! isset($this->resultList[$id])) {
            $this->resultList[$id] = $this->loadResult($this->createUrl($args));
        }
        return $this->resultList[$id];
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new NullResult($url);
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $this->getElementTag());
        $element->setAttribute(Module::ATTR_NAME, $this->getName());
        $element->setAttribute(Module::ATTR_ID, $this->getId());
        $element->setAttribute(Module::ATTR_HREF, str_replace('farah://', '/getAsset.php/', $this->getId()));
        foreach ($this->getPathResolver()->getPathMap() as $asset) {
            if ($asset !== $this) {
                $element->appendChild($asset->toElement($targetDoc));
            }
        }
        return $element;
    }

    public function lookupLinkedStylesheets(): array
    {
        if ($this->linkedStylesheets === null) {
            $this->linkedStylesheets = $this->loadLinkedStylesheets();
        }
        return $this->linkedStylesheets;
    }

    protected function loadLinkedStylesheets(): array
    {
        $ret = [];
        foreach ($this->getMetaChildren() as $child) {
            if ($child instanceof LinkStylesheetInstruction) {
                $asset = $child->getReferencedStylesheetAsset();
                $ret[(string) $asset] = $asset;
            }
        }
        return $ret;
    }

    public function lookupLinkedScripts(): array
    {
        if ($this->linkedScripts === null) {
            $this->linkedScripts = $this->loadLinkedScripts();
        }
        return $this->linkedScripts;
    }

    protected function loadLinkedScripts(): array
    {
        $ret = [];
        foreach ($this->getMetaChildren() as $child) {
            if ($child instanceof LinkScriptInstruction) {
                $asset = $child->getReferencedScriptAsset();
                $ret[(string) $asset] = $asset;
            }
        }
        return $ret;
    }
}


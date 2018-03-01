<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetUses\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\Element\ModuleElement;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\ParameterFilters\AllowAllFilter;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\NullResult;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;
use DOMElement;
use Slothsoft\Farah\Module\Element\ModuleElementInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericAsset extends ModuleElement implements AssetInterface 
{
    use DOMWriterDocumentFromElementTrait;

   
    private $path;
    
    private $resultList = [];

    private $pathResolver;

    private $parameterFilter;

    public function initAsset(Module $ownerModule, LeanElement $element, array $children, FarahUrlPath $path)
    {
        $this->initModuleElement($ownerModule, $element, $children);
        
        $this->path = $path;
    }
    
    public function getUrlPath(): FarahUrlPath
    {
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
    
    public function getAssetChildren() : array {
        return array_filter(
            $this->getChildren(),
            function(ModuleElementInterface $node) {
                return $node instanceof AssetInterface;
            },
            ARRAY_FILTER_USE_BOTH
        );
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
    public function filterArguments(FarahUrlArguments $args): bool
    {
        $ret = false;
        $filter = $this->getParameterFilter();
        foreach ($args->getNameList() as $key) {
            if (! $filter->isAllowedName($key)) {
                $args->delete($key);
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

    public function createUrl(FarahUrlArguments $args): FarahUrl
    {
        return $this->getOwnerModule()->createUrl(
            $this->getUrlPath(),
            $args
        );
    }
    public function lookupResultByArguments(FarahUrlArguments $args): ResultInterface {
        $args = $this->mergeWithManifestArguments($args);
        $id = (string) $args;
        if (!isset($this->resultList[$id])) {
            $this->resultList[$id] = $this->loadResult($this->createUrl($args));
        }
        return $this->resultList[$id];
    }
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new NullResult($url);
    }
    
    
    
    public function __toString() : string {
        return $this->getId();
    }
    public function toElement(DOMDocument $targetDoc) : DOMElement
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

}


<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Vector;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\HttpDownloadAssetException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableContainer;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use SplFileInfo;

class Asset implements AssetInterface {
    
    private ManifestInterface $ownerManifest;
    
    private LeanElement $manifestElement;
    
    private FarahUrlPath $urlPath;
    
    private AssetStrategies $strategies;
    
    private ExecutableContainer $executables;
    
    private ?Vector $assetChildren;
    
    public function __construct(ManifestInterface $ownerManifest, LeanElement $manifestElement, FarahUrlPath $urlPath, AssetStrategies $strategies) {
        $this->ownerManifest = $ownerManifest;
        $this->manifestElement = $manifestElement;
        $this->urlPath = $urlPath;
        $this->strategies = $strategies;
        $this->executables = new ExecutableContainer();
        $this->assetChildren = null;
    }
    
    public function __toString(): string {
        return (string) $this->createUrl();
    }
    
    public function getManifest(): ManifestInterface {
        return $this->ownerManifest;
    }
    
    public function getManifestElement(): LeanElement {
        return $this->manifestElement;
    }
    
    public function getChildManifestElement(string $name): LeanElement {
        return $this->strategies->pathResolver->resolvePath($this, $name);
    }
    
    public function getAssetChildren(): iterable {
        if ($this->assetChildren === null) {
            $this->assetChildren = new Vector();
            /** @var string $childPath */
            foreach ($this->strategies->pathResolver->loadChildren($this) as $childPath) {
                $childAsset = $this->traverseTo($childPath);
                if ($childAsset->isImportSelfInstruction()) {
                    $referencedAsset = Module::resolveToAsset($childAsset->createRealUrl());
                    yield $referencedAsset;
                    $this->assetChildren[] = $referencedAsset;
                }
                if ($childAsset->isImportChildrenInstruction()) {
                    $referencedAsset = Module::resolveToAsset($childAsset->createRealUrl());
                    foreach ($referencedAsset->getAssetChildren() as $importedAsset) {
                        yield $importedAsset;
                        $this->assetChildren[] = $importedAsset;
                    }
                }
                yield $childAsset;
                $this->assetChildren[] = $childAsset;
            }
        } else {
            yield from $this->assetChildren;
        }
    }
    
    public function traverseTo(string $path): AssetInterface {
        $path = preg_replace('~^/+~', '', $path);
        
        if ($path === '') {
            return $this;
        }
        
        $position = strpos($path, FarahUrlPath::SEPARATOR);
        
        if ($position === false) {
            $name = $path;
            $descendantPath = '';
        } else {
            $name = substr($path, 0, $position);
            $descendantPath = substr($path, $position + 1);
        }
        
        $asset = $this->ownerManifest->lookupAsset($this->urlPath->withLastSegment($name));
        
        if ($descendantPath === '') {
            return $asset;
        } else {
            return $asset->traverseTo($descendantPath);
        }
    }
    
    public function createCacheFile(string $fileName, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerManifest->createCacheFile($fileName, $this->getUrlPath(), $args, $fragment);
    }
    
    public function createDataFile(string $fileName, $args = null, $fragment = null): SplFileInfo {
        return $this->ownerManifest->createDataFile($fileName, $this->getUrlPath(), $args, $fragment);
    }
    
    public function createUrl($args = null, $fragment = null): FarahUrl {
        return $this->ownerManifest->createUrl($this->getUrlPath(), $args, $fragment);
    }
    
    private const URL_PREFERS_GLOBAL_PARAMS = false;
    
    public function createRealUrl($args = null, $fragment = null): FarahUrl {
        if (! $this->manifestElement->hasAttribute(Manifest::ATTR_REFERENCE)) {
            return $this->createUrl($args, $fragment);
        }
        
        $ref = $this->manifestElement->getAttribute(Manifest::ATTR_REFERENCE);
        $url = FarahUrl::createFromReference($ref);
        
        if ($args !== null) {
            $url = $url->withAdditionalQueryArguments($args instanceof FarahUrlArguments ? $args : FarahUrlArguments::createFromQuery((string) $args), self::URL_PREFERS_GLOBAL_PARAMS);
        }
        
        if ($fragment !== null) {
            $url = $url->withStreamIdentifier($fragment instanceof FarahUrlStreamIdentifier ? $fragment : FarahUrlStreamIdentifier::createFromString((string) $fragment));
        }
        
        return Module::resolveToResult($url)->createRealUrl();
    }
    
    public function getUrlPath(): FarahUrlPath {
        return $this->urlPath;
    }
    
    public function getFileInfo(): SplFileInfo {
        return new SplFileInfo($this->manifestElement->getAttribute(Manifest::ATTR_REALPATH));
    }
    
    public function lookupExecutable(FarahUrlArguments $args = null): ExecutableInterface {
        if ($args === null) {
            $args = $this->getManifestArguments();
        } else {
            $args = $args->withArguments($this->getManifestArguments());
        }
        $args = $this->applyParameterFilter($args);
        if (! $this->executables->has($args)) {
            $this->executables->put($args, $this->createExecutable($args));
        }
        return $this->executables->get($args);
    }
    
    private function getManifestArguments(): FarahUrlArguments {
        $data = [];
        foreach ($this->getSuppliedParameters() as $key => $val) {
            $data[$key] = $val;
        }
        foreach ($this->getAssetChildren() as $child) {
            if ($child->isParameterSupplierInstruction()) {
                foreach ($child->getSuppliedParameters() as $key => $val) {
                    $data[$key] = $val;
                }
            }
        }
        return FarahUrlArguments::createFromValueList($data);
    }
    
    public function getSuppliedParameters(): iterable {
        return $this->strategies->parameterSupplier->supplyParameters($this);
    }
    
    private function applyParameterFilter(FarahUrlArguments $args): FarahUrlArguments {
        $valueList = $args->getValueList();
        $hasChanged = false;
        $filter = $this->strategies->parameterFilter;
        foreach ($valueList as $name => $value) {
            if (! $filter->isAllowedName($name)) {
                unset($valueList[$name]);
                $hasChanged = true;
            }
        }
        foreach ($filter->getValueSanitizers() as $name => $sanitizer) {
            if (isset($valueList[$name])) {
                $value = $sanitizer->apply($valueList[$name]);
                if ($valueList[$name] !== $value) {
                    $valueList[$name] = $value;
                    $hasChanged = true;
                }
            } else {
                $valueList[$name] = $sanitizer->getDefault();
                $hasChanged = true;
            }
        }
        return $hasChanged ? FarahUrlArguments::createFromValueList($valueList) : $args;
    }
    
    private function createExecutable(FarahUrlArguments $args) {
        try {
            $strategies = $this->strategies->executableBuilder->buildExecutableStrategies($this, $args);
            return new Executable($this, $args, $strategies);
        } catch (HttpDownloadAssetException $e) {
            $strategies = $e->getStrategies();
            $executable = new Executable($this, $args, $strategies);
            throw new HttpDownloadException($executable->lookupResult(FarahUrlStreamIdentifier::createEmpty()), $e->isInline());
        }
    }
    
    public function isImportSelfInstruction(): bool {
        return $this->strategies->instruction->isImportSelf($this);
    }
    
    public function isImportChildrenInstruction(): bool {
        return $this->strategies->instruction->isImportChildren($this);
    }
    
    public function isUseManifestInstruction(): bool {
        return $this->strategies->instruction->isUseManifest($this);
    }
    
    public function isUseDocumentInstruction(): bool {
        return $this->strategies->instruction->isUseDocument($this);
    }
    
    public function isUseTemplateInstruction(): bool {
        return $this->strategies->instruction->isUseTemplate($this);
    }
    
    public function isLinkScriptInstruction(): bool {
        return $this->strategies->instruction->isLinkScript($this);
    }
    
    public function isLinkModuleInstruction(): bool {
        return $this->strategies->instruction->isLinkModule($this);
    }
    
    public function isLinkContentInstruction(): bool {
        return $this->strategies->instruction->isLinkContent($this);
    }
    
    public function isLinkDictionaryInstruction(): bool {
        return $this->strategies->instruction->isLinkDictionary($this);
    }
    
    public function isLinkStylesheetInstruction(): bool {
        return $this->strategies->instruction->isLinkStylesheet($this);
    }
    
    public function isParameterSupplierInstruction(): bool {
        return $this->strategies->instruction->isParameterSupplier($this);
    }
    
    public function normalizeManifestElement(LeanElement $child): void {
        $this->ownerManifest->normalizeManifestElement($this->manifestElement, $child);
    }
}


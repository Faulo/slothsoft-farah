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
    
    public function getAssetChildren(): iterable {
        if ($this->assetChildren === null) {
            $this->assetChildren = new Vector();
            foreach ($this->strategies->pathResolver->loadChildren($this) as $childPath) {
                $childAsset = $this->traverseTo($childPath);
                if ($childAsset->isImportSelfInstruction()) {
                    $referencedAsset = Module::resolveToAsset($childAsset->getReferencedUrl());
                    yield $referencedAsset;
                    $this->assetChildren[] = $referencedAsset;
                }
                if ($childAsset->isImportChildrenInstruction()) {
                    $referencedAsset = Module::resolveToAsset($childAsset->getReferencedUrl());
                    foreach ($referencedAsset->getAssetChildren() as $importedAsset) {
                        yield $importedAsset;
                        $this->assetChildren[] = $importedAsset;
                    }
                }
                yield $childAsset;
                $this->assetChildren[] = $childAsset;
            }
        } else {
            foreach ($this->assetChildren as $asset) {
                yield $asset;
            }
        }
    }
    
    public function traverseTo(string $path): AssetInterface {
        $path = preg_replace('~^/+~', '', $path);
        
        if ($path === '') {
            return $this;
        }
        
        list ($name, $descendantPath) = explode('/', "$path/", 2);
        $element = $this->strategies->pathResolver->resolvePath($this, $name);
        $asset = $this->ownerManifest->createAsset($element);
        
        if ($descendantPath === '') {
            return $asset;
        } else {
            return $asset->traverseTo($descendantPath);
        }
    }
    
    public function createUrl($args = null, $fragment = null): FarahUrl {
        return $this->ownerManifest->createUrl($this->getUrlPath(), $args, $fragment);
    }
    
    public function getUrlPath(): FarahUrlPath {
        return $this->urlPath;
    }
    
    public function getFileInfo(): SplFileInfo {
        return new SplFileInfo($this->manifestElement->getAttribute('realpath'));
    }
    
    public function lookupExecutable(FarahUrlArguments $args = null): ExecutableInterface {
        if ($args === null) {
            $args = $this->getManifestArguments();
        } else {
            // $args = $this->getManifestArguments()->withArguments($args);
            $args = $args->withArguments($this->getManifestArguments());
        }
        $args = $this->applyParameterFilter($args);
        if ($url = $this->getReferencedUrl()) {
            return Module::resolveToExecutable($url->withQueryArguments($args));
        }
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
    
    public function isLinkStylesheetInstruction(): bool {
        return $this->strategies->instruction->isLinkStylesheet($this);
    }
    
    public function isParameterSupplierInstruction(): bool {
        return $this->strategies->instruction->isParameterSupplier($this);
    }
    
    public function normalizeManifestElement(LeanElement $child): void {
        $this->ownerManifest->normalizeManifestElement($this->manifestElement, $child);
    }
    
    private function getReferencedUrl(): ?FarahUrl {
        $ref = $this->manifestElement->getAttribute('ref', '');
        return $ref === '' ? null : FarahUrl::createFromReference($ref, $this->createUrl());
    }
}


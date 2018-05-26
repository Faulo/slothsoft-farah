<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Vector;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Executable\ExecutableContainer;
use Slothsoft\Farah\Module\Executable\Executable;

class Asset implements AssetInterface
{
    /**
     * @var ManifestInterface
     */
    private $ownerManifest;
    
    /**
     * @var LeanElement
     */
    private $manifestElement;
    
    /**
     * @var FarahUrlPath
     */
    private $urlPath;
    
    /**
     * @var AssetStrategies
     */
    private $strategies;
    
    /**
     * @var ExecutableContainer
     */
    private $executables;
    
    /**
     * @var Vector
     */
    private $assetChildren;
    
    /**
     * @var UseInstructionCollection
     */
    private $useInstructions;
    
    /**
     * @var LinkInstructionCollection
     */
    private $linkInstructions;
    
    
    public function __construct(ManifestInterface $ownerManifest, LeanElement $manifestElement, FarahUrlPath $urlPath, AssetStrategies $strategies) {
        $this->ownerManifest = $ownerManifest;
        $this->manifestElement = $manifestElement;
        $this->urlPath = $urlPath;
        $this->strategies = $strategies;
        $this->executables = new ExecutableContainer();
        $this->assetChildren = null;
    }
    public function __toString() : string {
        return (string) $this->createUrl();
    }
    public function getManifestElement() : LeanElement
    {
        return $this->manifestElement;
    }
    
    public function getAssetChildren() : iterable
    {
        if ($this->assetChildren === null) {
            $this->assetChildren = new Vector();
            foreach ($this->strategies->pathResolver->loadChildren($this) as $childPath) {
                $childAsset = $this->traverseTo($childPath);
                if ($childAsset->isImportChildrenInstruction() or $childAsset->isImportSelfInstruction()) {
                    $importedAsset = $childAsset->getReferencedInstructionAsset();
                    if ($childAsset->isImportSelfInstruction()) {
                        yield $importedAsset;
                        $this->assetChildren[] = $importedAsset;
                    }
                    if ($childAsset->isImportChildrenInstruction()) {
                        foreach ($importedAsset->getAssetChildren() as $importedChildAsset) {
                            yield $importedChildAsset;
                            $this->assetChildren[] = $importedChildAsset;
                        }
                    }
                } else {
                    yield $childAsset;
                    $this->assetChildren[] = $childAsset;
                }
            }
        } else {
            return $this->assetChildren;
        }
    }
    
    public function traverseTo(string $path) : AssetInterface
    {
        $path = preg_replace('~^/+~', '', $path);
        
        if ($path === '') {
            return $this;
        }
        
        list($name, $descendantPath) = explode('/', "$path/", 2);
        $element = $this->strategies->pathResolver->resolvePath($this, $name);
        $asset = $this->ownerManifest->createAsset($element);
        
        if ($descendantPath === '') {
            return $asset;
        } else {
            return $asset->traverseTo($descendantPath);
        }
    }
    
    public function createUrl($args = null, $fragment = null) : FarahUrl
    {
        return $this->ownerManifest->createUrl($this->getUrlPath(), $args, $fragment);
    }
    
    public function getUrlPath() : FarahUrlPath {
        return $this->urlPath;
    }
    
    public function lookupExecutable(FarahUrlArguments $args = null) : ExecutableInterface
    {
        if ($args === null) {
            $args = FarahUrlArguments::createEmpty();
        }
        if (! $this->executables->has($args)) {
            $this->executables->put($args, $this->createExecutable($args));
        }
        return $this->executables->get($args);
    }
    private function createExecutable(FarahUrlArguments $args) {
        $strategies = $this->strategies->executableBuilder->buildExecutableStrategies($this, $args);
        return new Executable($this, $args, $strategies);
    }
    
    public function isImportSelfInstruction() : bool {
        return $this->strategies->instruction->isImportSelf($this);
    }
    public function isImportChildrenInstruction() : bool {
        return $this->strategies->instruction->isImportChildren($this);
    }
    public function isUseManifestInstruction() : bool {
        return $this->strategies->instruction->isUseManifest($this);
    }
    public function isUseDocumentInstruction() : bool {
        return $this->strategies->instruction->isUseDocument($this);
    }
    public function isUseTemplateInstruction() : bool {
        return $this->strategies->instruction->isUseTemplate($this);
    }
    public function isLinkScriptInstruction() : bool {
        return $this->strategies->instruction->isLinkScript($this);
    }
    public function isLinkStylesheetInstruction() : bool {
        return $this->strategies->instruction->isLinkStylesheet($this);
    }
    
    public function getReferencedInstructionAsset() : AssetInterface {
        return $this->strategies->instruction->getReferencedAsset($this);
    }
    
    public function getUseInstructions(): UseInstructionCollection {
        if ($this->useInstructions === null) {
            $this->useInstructions = $this->collectUseInstructions();
        }
        return $this->useInstructions;
    }
    private function collectUseInstructions() : UseInstructionCollection {
        $instructions = new UseInstructionCollection();
        foreach ($this->getAssetChildren() as $asset) {
            if ($asset->isUseManifestInstruction()) {
                $instructions->manifestAssets[] = $asset;
            }
            if ($asset->isUseDocumentInstruction()) {
                $instructions->documentAssets[] = $asset;
            }
            if ($asset->isUseTemplateInstruction()) {
                $instructions->templateAsset = $asset;
            }
        }
        return $instructions;
    }
    
    public function getLinkInstructions(): LinkInstructionCollection {
        if ($this->linkInstructions === null) {
            $this->linkInstructions = $this->collectLinkInstructions();
        }
        return $this->linkInstructions;
    }
    private function collectLinkInstructions() : LinkInstructionCollection {
        $instructions = new LinkInstructionCollection();
        foreach ($this->getAssetChildren() as $asset) {
            $instructions->mergeWith($asset->getLinkInstructions());
            if ($asset->isLinkStylesheetInstruction()) {
                $instructions->stylesheetAssets[] = $asset->getReferencedInstructionAsset();
            }
            if ($asset->isLinkScriptInstruction()) {
                $instructions->scriptAssets[] = $asset->getReferencedInstructionAsset();
            }
        }
        return $instructions;
    }
    public function applyParameterFilter(FarahUrlArguments $args): FarahUrlArguments
    {
        return $args;
    }
    
    public function normalizeManifestElement(LeanElement $child) : void {
        $this->ownerManifest->normalizeManifestElement($this->manifestElement, $child);
    }

}


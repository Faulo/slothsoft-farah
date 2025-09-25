<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Asset\AssetStrategies;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\DaemonExecutableBuilder;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\FromFilesystemExecutableBuilder;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\FromManifestExecutableBuilder;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\FromReferenceExecutableBuilder;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\NullExecutableBuilder;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\FromManifestInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\ImportChildrenInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\InstructionStrategyInterface;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\LinkModuleInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\LinkScriptInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\LinkStylesheetInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\NullInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\ParameterSupplierInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\UseDocumentInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\UseManifestInstruction;
use Slothsoft\Farah\Module\Asset\InstructionStrategy\UseTemplateInstruction;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\AllowAllParameterFilter;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\DenyAllParameterFilter;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\FromManifestParameterFilter;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\ParameterFilterStrategyInterface;
use Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy\FromManifestParameterSupplier;
use Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy\FromReferenceParameterSupplier;
use Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy\NullParameterSupplier;
use Slothsoft\Farah\Module\Asset\ParameterSupplierStrategy\ParameterSupplierStrategyInterface;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\FromFilesystemPathResolver;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\FromManifestPathResolver;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\FromSubManifestPathResolver;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\NullPathResolver;
use Slothsoft\Farah\Module\Asset\PathResolverStrategy\PathResolverStrategyInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;

class DefaultAssetBuilder implements AssetBuilderStrategyInterface {
    
    public function normalizeElement(LeanElement $element, ?LeanElement $parent = null): void {
        $tag = $element->getTag();
        if (! $element->hasAttribute(Manifest::ATTR_NAME)) {
            $element->setAttribute(Manifest::ATTR_NAME, uniqid('asset-'));
        }
        if (! $element->hasAttribute(Manifest::ATTR_PATH)) {
            $path = $element->getAttribute(Manifest::ATTR_NAME);
            if ($tag === Manifest::TAG_RESOURCE and $element->hasAttribute(Manifest::ATTR_TYPE)) {
                $extension = MimeTypeDictionary::guessExtension($element->getAttribute(Manifest::ATTR_TYPE));
                if ($extension !== '') {
                    $path .= ".$extension";
                }
            }
            $element->setAttribute(Manifest::ATTR_PATH, $path);
        }
        if ($parent) {
            if (! $element->hasAttribute(Manifest::ATTR_ASSETPATH)) {
                $element->setAttribute(Manifest::ATTR_ASSETPATH, $parent->getAttribute(Manifest::ATTR_ASSETPATH) . FarahUrlPath::SEPARATOR . $element->getAttribute(Manifest::ATTR_NAME));
            }
            if (! $element->hasAttribute(Manifest::ATTR_REALPATH)) {
                $element->setAttribute(Manifest::ATTR_REALPATH, $parent->getAttribute(Manifest::ATTR_REALPATH) . DIRECTORY_SEPARATOR . $element->getAttribute(Manifest::ATTR_PATH));
            }
        }
        
        switch ($tag) {
            // "meta" assets
            case Manifest::TAG_IMPORT:
                $executableBuilder = NullExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = DenyAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = ImportChildrenInstruction::class;
                break;
            case Manifest::TAG_PARAM:
                $executableBuilder = NullExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = DenyAllParameterFilter::class;
                $parameterSupplier = FromManifestParameterSupplier::class;
                $instruction = ParameterSupplierInstruction::class;
                break;
            case Manifest::TAG_USE_DOCUMENT:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = DenyAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = UseDocumentInstruction::class;
                break;
            case Manifest::TAG_USE_MANIFEST:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = FromReferenceParameterSupplier::class;
                $instruction = UseManifestInstruction::class;
                break;
            case Manifest::TAG_USE_TEMPLATE:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = FromReferenceParameterSupplier::class;
                $instruction = UseTemplateInstruction::class;
                break;
            case Manifest::TAG_LINK_STYLESHEET:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = FromReferenceParameterSupplier::class;
                $instruction = LinkStylesheetInstruction::class;
                break;
            case Manifest::TAG_LINK_SCRIPT:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = FromReferenceParameterSupplier::class;
                $instruction = LinkScriptInstruction::class;
                break;
            case Manifest::TAG_LINK_MODULE:
                $executableBuilder = FromReferenceExecutableBuilder::class;
                $pathResolver = NullPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = FromReferenceParameterSupplier::class;
                $instruction = LinkModuleInstruction::class;
                break;
            // "physical" assets
            case Manifest::TAG_RESOURCE:
                $executableBuilder = FromFilesystemExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = DenyAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            case Manifest::TAG_DIRECTORY:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = FromManifestParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            case Manifest::TAG_RESOURCE_DIRECTORY:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromFilesystemPathResolver::class;
                $parameterFilter = FromManifestParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            case Manifest::TAG_MANIFEST_DIRECTORY:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromSubManifestPathResolver::class;
                $parameterFilter = FromManifestParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            // "virtual" assets
            case Manifest::TAG_DAEMON:
                $executableBuilder = DaemonExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = DenyAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = NullInstruction::class;
                break;
            case Manifest::TAG_CUSTOM_ASSET:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            case Manifest::TAG_ASSET_ROOT:
            case Manifest::TAG_FRAGMENT:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = AllowAllParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
            case Manifest::TAG_EXTERNAL_RESOURCE: // not implemented?
            case Manifest::TAG_SOURCE: // not implemented?
            case Manifest::TAG_OPTIONS: // not implemented?
            case Manifest::TAG_CLOSURE: // not implemented?
            default:
                $executableBuilder = FromManifestExecutableBuilder::class;
                $pathResolver = FromManifestPathResolver::class;
                $parameterFilter = FromManifestParameterFilter::class;
                $parameterSupplier = NullParameterSupplier::class;
                $instruction = FromManifestInstruction::class;
                break;
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_USE)) {
            $element->setAttribute(Manifest::ATTR_USE, Manifest::ATTR_USE_MANIFEST);
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_EXECUTABLE_BUILDER)) {
            $element->setAttribute(Manifest::ATTR_EXECUTABLE_BUILDER, $executableBuilder);
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PATH_RESOLVER)) {
            $element->setAttribute(Manifest::ATTR_PATH_RESOLVER, $pathResolver);
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PARAMETER_FILTER)) {
            $element->setAttribute(Manifest::ATTR_PARAMETER_FILTER, $parameterFilter);
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PARAMETER_SUPPLIER)) {
            $element->setAttribute(Manifest::ATTR_PARAMETER_SUPPLIER, $parameterSupplier);
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_INSTRUCTION)) {
            $element->setAttribute(Manifest::ATTR_INSTRUCTION, $instruction);
        }
        
        foreach ($element->getChildren() as $child) {
            $this->normalizeElement($child, $element);
        }
    }
    
    private static $services = [];
    
    public function buildAssetStrategies(ManifestInterface $ownerManifest, LeanElement $element): AssetStrategies {
        if (! $element->hasAttribute(Manifest::ATTR_EXECUTABLE_BUILDER)) {
            throw new \UnexpectedValueException(sprintf('Missing "%s" attribute on element: %s', Manifest::ATTR_EXECUTABLE_BUILDER, serialize($element)));
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PATH_RESOLVER)) {
            throw new \UnexpectedValueException(sprintf('Missing "%s" attribute on element: %s', Manifest::ATTR_PATH_RESOLVER, serialize($element)));
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PARAMETER_FILTER)) {
            throw new \UnexpectedValueException(sprintf('Missing "%s" attribute on element: %s', Manifest::ATTR_PARAMETER_FILTER, serialize($element)));
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_PARAMETER_SUPPLIER)) {
            throw new \UnexpectedValueException(sprintf('Missing "%s" attribute on element: %s', Manifest::ATTR_PARAMETER_SUPPLIER, serialize($element)));
        }
        
        if (! $element->hasAttribute(Manifest::ATTR_INSTRUCTION)) {
            throw new \UnexpectedValueException(sprintf('Missing "%s" attribute on element: %s', Manifest::ATTR_INSTRUCTION, serialize($element)));
        }
        
        return new AssetStrategies($this->newExecutableBuilder($element->getAttribute(Manifest::ATTR_EXECUTABLE_BUILDER)), $this->newPathResolver($element->getAttribute(Manifest::ATTR_PATH_RESOLVER)), $this->newParameterFilter($element->getAttribute(Manifest::ATTR_PARAMETER_FILTER)), $this->newParameterSupplier($element->getAttribute(Manifest::ATTR_PARAMETER_SUPPLIER)), $this->newInstruction($element->getAttribute(Manifest::ATTR_INSTRUCTION)));
    }
    
    private function newExecutableBuilder(string $className): ExecutableBuilderStrategyInterface {
        if (! isset(static::$services[$className])) {
            static::$services[$className] = new $className();
        }
        return static::$services[$className];
    }
    
    private function newPathResolver(string $className): PathResolverStrategyInterface {
        if (! isset(static::$services[$className])) {
            static::$services[$className] = new $className();
        }
        return static::$services[$className];
    }
    
    private function newParameterFilter(string $className): ParameterFilterStrategyInterface {
        if (! isset(static::$services[$className])) {
            static::$services[$className] = new $className();
        }
        return static::$services[$className];
    }
    
    private function newParameterSupplier(string $className): ParameterSupplierStrategyInterface {
        if (! isset(static::$services[$className])) {
            static::$services[$className] = new $className();
        }
        return static::$services[$className];
    }
    
    private function newInstruction(string $className): InstructionStrategyInterface {
        if (! isset(static::$services[$className])) {
            static::$services[$className] = new $className();
        }
        return static::$services[$className];
    }
}


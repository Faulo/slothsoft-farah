<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Enhancements;

use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\ExternalResourceAsset;
use Slothsoft\Farah\Module\Node\Asset\FragmentAsset;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\ResourceAsset;

trait AssetBuilderTrait {

    protected function buildExternalResource(string $name, string $href, string $type = '*/*'): ExternalResourceAsset
    {
        $element = LeanElement::createOneFromArray(Module::TAG_EXTERNAL_RESOURCE, [
            'name' => $name,
            'src' => $href,
            'type' => $type
        ]);
        return $this->createChildNode($element);
    }

    protected function buildFragment(string $name, array $useDocumentList, string $useTemplate): FragmentAsset
    {
        $children = [];
        foreach ($useDocumentList as $key => $useDocument) {
            $attributes = [];
            $attributes['ref'] = $useDocument;
            if (is_string($key)) {
                $attributes['as'] = $key;
            }
            $children[] = LeanElement::createOneFromArray(Module::TAG_USE_DOCUMENT, $attributes);
        }
        $children[] = LeanElement::createOneFromArray(Module::TAG_USE_TEMPLATE, [
            'ref' => $useTemplate
        ]);
        
        $element = LeanElement::createOneFromArray(Module::TAG_FRAGMENT, [
            'name' => $name
        ], $children);
        
        return $this->createChildNode($element);
    }

    protected function buildResourceFromContents(string $contents, string $name, string $type): ResourceAsset
    {
        $path = $name;
        if ($extension = MimeTypeDictionary::guessExtension($type)) {
            $path .= ".$extension";
        }
        return $this->buildResourceFromFile(HTTPFile::createFromString($contents, $path), $name, $type);
    }

    protected function buildResourceFromFile(HTTPFile $file, string $name, string $type): ResourceAsset
    {
        $tag = Module::TAG_RESOURCE;
        
        $attributes = [];
        $attributes['name'] = $name;
        $attributes['path'] = $file->getName();
        $attributes['realpath'] = $file->getPath();
        $attributes['type'] = $type;
        
        $element = LeanElement::createOneFromArray($tag, $attributes);
        
        return $this->createChildNode($element);
    }
}


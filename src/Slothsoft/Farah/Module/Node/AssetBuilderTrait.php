<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Asset\ExternalDocumentAsset;
use Slothsoft\Farah\Module\Node\Asset\FragmentAsset;

trait AssetBuilderTrait {

    protected function buildExternalDocument(string $name, string $href): ExternalDocumentAsset
    {
        $element = LeanElement::createOneFromArray(Module::TAG_EXTERNAL_DOCUMENT, [
            'name' => $name,
            'href' => $href
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
}


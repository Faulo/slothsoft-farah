<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Throwable;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

class XmlTreeLoader implements TreeLoaderStrategyInterface {
    
    public function loadTree(ManifestInterface $context): LeanElement {
        $xmlFile = $context->createManifestFile('manifest.xml');
        
        $tmpFile = $context->createCacheFile('manifest.tmp', null, FarahUrlArguments::createFromValueList([
            'path' => $xmlFile->getRealPath()
        ]));
        
        if ($tmpFile->isFile()) {
            if ($tmpFile->getMTime() > $xmlFile->getMTime()) {
                try {
                    $element = unserialize(file_get_contents((string) $tmpFile), [
                        'allowed_classes' => [
                            LeanElement::class
                        ]
                    ]);
                    if ($element and $element->getTag()) {
                        return $element;
                    }
                } catch (Throwable $e) {}
            }
        }
        
        $dom = new DOMHelper();
        $element = LeanElement::createTreeFromDOMDocument($dom->loadDocument($xmlFile->getRealPath()));
        if ($context) {
            $context->normalizeManifestTree($element);
            file_put_contents((string) $tmpFile, serialize($element));
        }
        return $element;
    }
}


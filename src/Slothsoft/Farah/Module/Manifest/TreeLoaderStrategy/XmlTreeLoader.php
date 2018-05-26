<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Throwable;

class XmlTreeLoader implements TreeLoaderStrategyInterface
{

    public function loadTree(?ManifestInterface $context, string $manifestDirectory): LeanElement
    {
        $xmlFile = $manifestDirectory . DIRECTORY_SEPARATOR . 'manifest.xml';
        $tmpFile = $manifestDirectory . DIRECTORY_SEPARATOR . 'manifest.tmp';
        
        if (file_exists($tmpFile)) {
            if (filemtime($tmpFile) > filemtime($xmlFile)) {
                try {
                    $element = unserialize(file_get_contents($tmpFile), [
                        'allowed_classes' => [
                            LeanElement::class
                        ]
                    ]);
                    if ($element) {
                        // return $element;
                    }
                } catch (Throwable $e) {}
            }
        }
        
        $dom = new DOMHelper();
        $element = LeanElement::createTreeFromDOMDocument($dom->loadDocument($xmlFile));
        if ($context) {
            $context->normalizeManifestTree($element);
            file_put_contents($tmpFile, serialize($element));
        }
        return $element;
    }
}


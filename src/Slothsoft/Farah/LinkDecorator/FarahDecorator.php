<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahDecorator implements LinkDecoratorInterface
{

    private $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function decorateDocument(DOMDocument $document, array $stylesheetAssetList, array $scriptAssetList)
    {
        $rootNode = $document->documentElement;
        foreach ($stylesheetAssetList as $assetId => $asset) {
            $rootNode->appendChild($asset->toElement($document));
        }
        foreach ($scriptAssetList as $assetId => $asset) {
            $rootNode->appendChild($asset->toElement($document));
        }
    }
}


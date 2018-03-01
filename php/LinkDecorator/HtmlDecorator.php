<?php
namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HtmlDecorator implements LinkDecoratorInterface
{
    private $namespace;
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
    
    public function decorateDocument(DOMDocument $document, array $stylesheetAssetList, array $scriptAssetList)
    {
        $rootNode = $document->getElementsByTagNameNS($this->namespace, 'head')->item(0) ?? $document->documentElement;
        foreach ($stylesheetAssetList as $assetId => $asset) {
            $assetLink = str_replace('farah://', '/getAsset.php/', $assetId);
            $node = $document->createElementNS($this->namespace, 'link');
            $node->setAttribute('href', $assetLink);
            $node->setAttribute('rel', 'stylesheet');
            $node->setAttribute('type', 'text/css');
            $rootNode->appendChild($node);
        }
        foreach ($scriptAssetList as $assetId => $asset) {
            $assetLink = str_replace('farah://', '/getAsset.php/', $assetId);
            $node = $document->createElementNS($this->namespace, 'script');
            $node->setAttribute('src', $assetLink);
            $node->setAttribute('defer', 'defer');
            $node->setAttribute('type', 'application/javascript');
            $rootNode->appendChild($node);
        }
    }
}


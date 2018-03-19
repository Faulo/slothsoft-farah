<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HtmlDecorator implements LinkDecoratorInterface
{

    private $namespace;

    private $targetDocument;

    private $rootNode;

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function setTarget(DOMDocument $document)
    {
        $this->targetDocument = $document;
        
        $this->rootNode = $document->getElementsByTagNameNS($this->namespace, 'head')->item(0) ?? $document->documentElement;
    }

    public function linkStylesheets(AssetInterface ...$stylesheets)
    {
        foreach ($stylesheets as $assetId => $asset) {
            $assetId = $asset->getId();
            $assetHref = str_replace('farah://', '/getAsset.php/', $assetId);
            $node = $this->targetDocument->createElementNS($this->namespace, 'link');
            $node->setAttribute('href', $assetHref);
            $node->setAttribute('rel', 'stylesheet');
            $node->setAttribute('type', 'text/css');
            $this->rootNode->appendChild($node);
        }
    }

    public function linkScripts(AssetInterface ...$scripts)
    {
        foreach ($scripts as $asset) {
            $assetId = $asset->getId();
            $assetHref = str_replace('farah://', '/getAsset.php/', $assetId);
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttribute('src', $assetHref);
            $node->setAttribute('defer', 'defer');
            $node->setAttribute('type', 'application/javascript');
            $this->rootNode->appendChild($node);
        }
    }
}


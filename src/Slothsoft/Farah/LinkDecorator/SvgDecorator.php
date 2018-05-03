<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use DOMDocument;
use Slothsoft\Core\DOMHelper;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SvgDecorator implements LinkDecoratorInterface
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
        
        $this->rootNode = $document->getElementsByTagNameNS($this->namespace, 'defs')->item(0) ?? $document->documentElement;
    }

    public function linkStylesheets(AssetInterface ...$stylesheets)
    {
        foreach ($stylesheets as $assetId => $asset) {
            $assetId = $asset->getId();
            $assetHref = str_replace('farah://', '/getAsset.php/', $assetId);
            
            $node = $this->targetDocument->createProcessingInstruction('xml-stylesheet', sprintf('type="text/css" href="%s"', $assetHref));
            $this->targetDocument->insertBefore($node, $this->targetDocument->firstChild);
        }
    }

    public function linkScripts(AssetInterface ...$scripts)
    {
        foreach ($scripts as $asset) {
            $assetId = $asset->getId();
            $assetHref = str_replace('farah://', '/getAsset.php/', $assetId);
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttributeNS(DOMHelper::NS_XLINK, 'xlink:href', $assetHref);
            $node->setAttribute('defer', 'defer');
            $this->rootNode->appendChild($node);
        }
    }
}

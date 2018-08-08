<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\FarahUrl\FarahUrl;
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

    public function linkStylesheets(FarahUrl ...$stylesheets)
    {
        foreach ($stylesheets as $url) {
            $href = str_replace('farah://', '/getAsset.php/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, 'link');
            $node->setAttribute('href', $href);
            $node->setAttribute('rel', 'stylesheet');
            $node->setAttribute('type', 'text/css');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkScripts(FarahUrl ...$scripts)
    {
        foreach ($scripts as $url) {
            $href = str_replace('farah://', '/getAsset.php/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttribute('src', $href);
            $node->setAttribute('defer', 'defer');
            $this->rootNode->appendChild($node);
        }
    }
    
    public function linkModules(FarahUrl ...$modules)
    {
        foreach ($modules as $url) {
            $href = str_replace('farah://', '/getAsset.php/', (string) $url);
            
            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttribute('src', $href);
            $node->setAttribute('type', 'module');
            $node->setAttribute('defer', 'defer');
            $node->setAttribute('async', 'async');
            $this->rootNode->appendChild($node);
        }
    }
}

